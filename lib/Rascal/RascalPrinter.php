<?php

namespace Rascal;

use Exception;
use PhpParser\Node;

class RascalPrinter extends BasePrinter
{

    private $filename = "";

    private $addLocations = false;

    private $addDeclarations = false;

    private $relativeLocations = false;

    private $addIds = false;

    private $addPhpDocs = false;

    private $idPrefix = "";

    private $insideTrait = false;

    private $insideFunction = false;

    private $currentFunction = "";

    private $currentClass = "";

    private $currentTrait = "";

    private $currentInterface = "";

    private $currentMethod = "";

    private $currentNamespace = "";

    private $varIsDecl = false;

    /**
     *
     * @param string $str
     * @param bool $locs
     * @param bool $rel
     * @param bool $ids
     * @param string $prefix
     * @param bool $docs
     */
    public function __construct($str, $locs, $rel, $ids, $prefix, $docs = false, $addDecl = false)
    {
        $this->filename = $str;
        $this->addLocations = $locs;
        $this->addDeclarations = $addDecl;
        $this->relativeLocations = $rel;
        $this->addIds = $ids;
        $this->idPrefix = $prefix;
        $this->addPhpDocs = $docs;
    }

    public function rascalizeString($str)
    {
        return addcslashes($str, "<>'\n\t\r\\\"");
    }

    private function addUniqueId()
    {
        $idToAdd = uniqid($this->idPrefix, true);
        return "@id=\"{$this->rascalizeString($idToAdd)}\"";
    }

    private function addDeclaration(Node $node)
    {
        $namespace = strtolower($this->currentNamespace);
        $class = strtolower($this->currentClass);
        $trait = strtolower($this->currentTrait);
        $interface = strtolower($this->currentInterface);
        $method = strtolower($this->currentMethod);
        $function = strtolower($this->currentFunction);

        // define the namespace name with a trailing slash, or leave empty for global namespace
        $ns = empty($namespace) ? '' : $namespace . "/";

        if (empty($class) && (!empty($trait) || !empty($interface))) {
            // use trait or interface as className when there is a currentTrait or currentInterface but no currentClass
            $class = !empty($trait) ? $trait : $interface;
        }

        $decl = "@decl=|php+%s:///%s|";
        if ($node instanceof Node\Stmt\Namespace_)
            return $this->rascalizeString(sprintf($decl, "namespace", $namespace));
        else if ($node instanceof Node\Stmt\Class_)
            return $this->rascalizeString(sprintf($decl, "class", $ns . $class));
        else if ($node instanceof Node\Stmt\Interface_)
            return $this->rascalizeString(sprintf($decl, "interface", $ns . $class));
        else if ($node instanceof Node\Stmt\Trait_)
            return $this->rascalizeString(sprintf($decl, "trait", $ns . $class));
        else if ($node instanceof Node\Stmt\PropertyProperty)
            return $this->rascalizeString(sprintf($decl, "field", $ns . $class . "/" . $node->name));
        else if ($node instanceof Node\Const_) {
            if ($class) // class constant
                return $this->rascalizeString(sprintf($decl, "classConstant", $ns . $class . "/" . $node->name));
            else //global constant
                return $this->rascalizeString(sprintf($decl, "constant", $ns . $node->name));
        }
        else if ($node instanceof Node\Stmt\ClassMethod)
            return $this->rascalizeString(sprintf($decl, "method", $ns . $class . "/" . $method));
        else if ($node instanceof Node\Stmt\Function_)
            return $this->rascalizeString(sprintf($decl, "function", $ns . $function));
        else if ($node instanceof Node\Stmt\StaticVar
                || ($node instanceof Node\Expr\Variable && $this->varIsDecl)) {
            $prefix = ($node->name instanceof Node\Expr) ? "unresolved+" : "";
            $name = ($node->name instanceof Node\Expr) ? "" : $node->name;
            // only declare variables that are inside an assign expression, and the name must not be an expression
            // (we are not able to handle this, atleast for now)
            if ($this->insideFunction) // function variable
                return $this->rascalizeString(sprintf($decl, $prefix . "functionVar", $ns . $function . "/" . $name));
            else if ($this->currentMethod) // method variable
                return $this->rascalizeString(sprintf($decl, $prefix . "methodVar", $ns . $class . "/" . $method . "/" . $name));
            else // global var
                return $this->rascalizeString(sprintf($decl, $prefix . "globalVar", $ns . $name));
        }
        else if ($node instanceof Node\Param) {
            if ($this->insideFunction) // function parameter
                return $this->rascalizeString(sprintf($decl, "functionParam", $ns . $function . "/" . $node->name));
            if ($this->currentMethod) // method parameter
                return $this->rascalizeString(sprintf($decl, "methodParam", $ns . $class . "/" . $method . "/" . $node->name));
        }
    }

    private function addLocationTag(Node $node)
    {
        if ($this->relativeLocations) {
            return "@at=|home://{$this->filename}|({$node->getOffset()},{$node->getLength()},<{$node->getLine()},0>,<{$node->getLine()},0>)";
        } else {
            return "@at=|file://{$this->filename}|({$node->getOffset()},{$node->getLength()},<{$node->getLine()},0>,<{$node->getLine()},0>)";
        }
    }

    /**
     * Try to extract data from PHPDoc.
     * If no PHPDoc found, return NULL
     *
     * Restriction: only for Class, Interface and Variable.
     *
     * @return string
     */
    private function addPhpDocForNode(Node $node)
    {
        $docString = "@phpdoc=\"%s\"";
        if ($node instanceof Node\Stmt\Class_ ||
            $node instanceof Node\Stmt\Interface_ ||
            $node instanceof Node\Expr\Variable
        ) {
            if ($doc = $node->getDocComment()) {
                return sprintf($docString, $this->rascalizeString($doc));
            }
        }
        return null;
    }

    private function annotateASTNode(Node $node)
    {
        $tagsToAdd = array();
        if ($this->addLocations)
            $tagsToAdd[] = $this->addLocationTag($node);
        if ($this->addDeclarations) {
            if ($decl = $this->addDeclaration($node)) {
                $tagsToAdd[] = $decl;
            }
        }
        if ($this->addIds)
            $tagsToAdd[] = $this->addUniqueId();
        if ($this->addPhpDocs) {
            if ($phpdoc = $this->addPhpDocForNode($node)) {
                $tagsToAdd[] = $phpdoc;
            }
        }

        if (count($tagsToAdd) > 0)
            return "[" . implode(",", $tagsToAdd) . "]";
        return "";
    }

    public function pprintArg(Node\Arg $node)
    {
        $argValue = $this->pprint($node->value);
        $byRef = $node->byRef ? "true" : "false";

        $fragment = "actualParameter(" . $argValue . "," . $byRef . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintConst(Node\Const_ $node)
    {
        $fragment = "const(\"" . $node->name . "\"," . $this->pprint($node->value) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayExpr(Node\Expr\Array_ $node)
    {
        $items = array();
        foreach ($node->items as $item)
            $items[] = $this->pprint($item);

        $fragment = "array([" . implode(",", $items) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayDimFetchExpr(Node\Expr\ArrayDimFetch $node)
    {
        $var = $this->pprint($node->var);
        $dim = $this->handlePossibleExpression($node->dim);

        $fragment = "fetchArrayDim(" . $var . "," . $dim . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayItemExpr(Node\Expr\ArrayItem $node)
    {
        $nodeValue = $this->pprint($node->value);
        $key = $this->handlePossibleExpression($node->key);
        $byRef = $node->byRef ? "true" : "false";

        $fragment = "arrayElement(" . $key . "," . $nodeValue . "," . $byRef . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignExpr(Node\Expr\Assign $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $this->varIsDecl = true;
        $assignVar = $this->pprint($node->var);
        $this->varIsDecl = false;

        $fragment = "assign(" . $assignVar . "," . $assignExpr . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseAndAssignOpExpr(Node\Expr\AssignOp\BitwiseAnd $node)
    {
        return $this->handleAssignOpExpression($node, "bitwiseAnd");
    }

    public function pprintBitwiseOrAssignOpExpr(Node\Expr\AssignOp\BitwiseOr $node)
    {
        return $this->handleAssignOpExpression($node, "bitwiseOr");
    }

    public function pprintBitwiseXorAssignOpExpr(Node\Expr\AssignOp\BitwiseXor $node)
    {
        return $this->handleAssignOpExpression($node, "bitwiseXor");
    }

    public function pprintConcatAssignOpExpr(Node\Expr\AssignOp\Concat $node)
    {
        return $this->handleAssignOpExpression($node, "concat");
    }

    public function pprintDivAssignOpExpr(Node\Expr\AssignOp\Div $node)
    {
        return $this->handleAssignOpExpression($node, "div");
    }

    public function pprintMinusAssignOpExpr(Node\Expr\AssignOp\Minus $node)
    {
        return $this->handleAssignOpExpression($node, "minus");
    }

    public function pprintModAssignOpExpr(Node\Expr\AssignOp\Mod $node)
    {
        return $this->handleAssignOpExpression($node, "\\mod");
    }

    public function pprintMulAssignOpExpr(Node\Expr\AssignOp\Mul $node)
    {
        return $this->handleAssignOpExpression($node, "mul");
    }

    public function pprintPlusAssignOpExpr(Node\Expr\AssignOp\Plus $node)
    {
        return $this->handleAssignOpExpression($node, "plus");
    }

    public function pprintShiftLeftAssignOpExpr(Node\Expr\AssignOp\ShiftLeft $node)
    {
        return $this->handleAssignOpExpression($node, "leftShift");
    }

    public function pprintShiftRightAssignOpExpr(Node\Expr\AssignOp\ShiftRight $node)
    {
        return $this->handleAssignOpExpression($node, "rightShift");
    }

    /**
     * @param Node\Expr\AssignOp $node
     * @param string $operation
     * @return string
     */
    private function handleAssignOpExpression(Node\Expr\AssignOp $node, $operation)
    {
        $assignExpr = $this->pprint($node->expr);
        $this->varIsDecl = true;
        $assignVar = $this->pprint($node->var);
        $this->varIsDecl = false;

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . "," . $operation . "())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignRefExpr(Node\Expr\AssignRef $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $this->varIsDecl = true;
        $assignVar = $this->pprint($node->var);
        $this->varIsDecl = false;

        $fragment = "refAssign(" . $assignVar . "," . $assignExpr . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseAndBinaryOpExpr(Node\Expr\BinaryOp\BitwiseAnd $node)
    {
        return $this->handleBinaryOpExpression($node, "bitwiseAnd");
    }

    public function pprintBitwiseOrBinaryOpExpr(Node\Expr\BinaryOp\BitwiseOr $node)
    {
        return $this->handleBinaryOpExpression($node, "bitwiseOr");
    }

    public function pprintBitwiseXorBinaryOpExpr(Node\Expr\BinaryOp\BitwiseXor $node)
    {
        return $this->handleBinaryOpExpression($node, "bitwiseXor");
    }

    public function pprintBooleanAndBinaryOpExpr(Node\Expr\BinaryOp\BooleanAnd $node)
    {
        return $this->handleBinaryOpExpression($node, "booleanAnd");
    }

    public function pprintBooleanOrBinaryOpExpr(Node\Expr\BinaryOp\BooleanOr $node)
    {
        return $this->handleBinaryOpExpression($node, "booleanOr");
    }

    public function pprintConcatBinaryOpExpr(Node\Expr\BinaryOp\Concat $node)
    {
        return $this->handleBinaryOpExpression($node, "concat");
    }

    public function pprintDivBinaryOpExpr(Node\Expr\BinaryOp\Div $node)
    {
        return $this->handleBinaryOpExpression($node, "div");
    }

    public function pprintEqualBinaryOpExpr(Node\Expr\BinaryOp\Equal $node)
    {
        return $this->handleBinaryOpExpression($node, "equal");
    }

    public function pprintGreaterBinaryOpExpr(Node\Expr\BinaryOp\Greater $node)
    {
        return $this->handleBinaryOpExpression($node, "gt");
    }

    public function pprintGreaterOrEqualBinaryOpExpr(Node\Expr\BinaryOp\GreaterOrEqual $node)
    {
        return $this->handleBinaryOpExpression($node, "geq");
    }

    public function pprintLogicalAndBinaryOpExpr(Node\Expr\BinaryOp\LogicalAnd $node)
    {
        return $this->handleBinaryOpExpression($node, "logicalAnd");
    }

    public function pprintLogicalOrBinaryOpExpr(Node\Expr\BinaryOp\LogicalOr $node)
    {
        return $this->handleBinaryOpExpression($node, "logicalOr");
    }

    public function pprintLogicalXorBinaryOpExpr(Node\Expr\BinaryOp\LogicalXor $node)
    {
        return $this->handleBinaryOpExpression($node, "logicalXor");
    }

    public function pprintIdenticalBinaryOpExpr(Node\Expr\BinaryOp\Identical $node)
    {
        return $this->handleBinaryOpExpression($node, "identical");
    }

    public function pprintMinusBinaryOpExpr(Node\Expr\BinaryOp\Minus $node)
    {
        return $this->handleBinaryOpExpression($node, "minus");
    }

    public function pprintModBinaryOpExpr(Node\Expr\BinaryOp\Mod $node)
    {
        return $this->handleBinaryOpExpression($node, "\\mod");
    }

    public function pprintMulBinaryOpExpr(Node\Expr\BinaryOp\Mul $node)
    {
        return $this->handleBinaryOpExpression($node, "mul");
    }

    public function pprintShiftLeftBinaryOpExpr(Node\Expr\BinaryOp\ShiftLeft $node)
    {
        return $this->handleBinaryOpExpression($node, "leftShift");
    }

    public function pprintShiftRightBinaryOpExpr(Node\Expr\BinaryOp\ShiftRight $node)
    {
        return $this->handleBinaryOpExpression($node, "rightShift");
    }

    public function pprintSmallerBinaryOpExpr(Node\Expr\BinaryOp\Smaller $node)
    {
        return $this->handleBinaryOpExpression($node, "lt");
    }

    public function pprintSmallerOrEqualBinaryOpExpr(Node\Expr\BinaryOp\SmallerOrEqual $node)
    {
        return $this->handleBinaryOpExpression($node, "leq");
    }

    public function pprintNotEqualBinaryOpExpr(Node\Expr\BinaryOp\NotEqual $node)
    {
        return $this->handleBinaryOpExpression($node, "notEqual");
    }

    public function pprintNotIdenticalBinaryOpExpr(Node\Expr\BinaryOp\NotIdentical $node)
    {
        return $this->handleBinaryOpExpression($node, "notIdentical");
    }

    public function pprintPlusBinaryOpExpr(Node\Expr\BinaryOp\Plus $node)
    {
        return $this->handleBinaryOpExpression($node, "plus");
    }

    /**
     * @param Node\Expr\BinaryOp $node
     * @param string $operation
     * @return string
     */
    private function handleBinaryOpExpression(Node\Expr\BinaryOp $node, $operation)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . "," . $operation . "())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBooleanNotExpr(Node\Expr\BooleanNot $node)
    {
        return $this->handleUnaryOpExpression($node, "booleanNot");
    }

    public function pprintBitwiseNotExpr(Node\Expr\BitwiseNot $node)
    {
        return $this->handleUnaryOpExpression($node, "bitwiseNot");
    }

    private function handleUnaryOpExpression(Node\Expr $node, $operation)
    {
        $expr = $this->pprint($node->expr);

        $fragment = "unaryOperation(" . $expr . "," . $operation . "())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayCastExpr(Node\Expr\Cast\Array_ $node)
    {
        return $this->handleCastExpression($node, "array");
    }

    public function pprintBoolCastExpr(Node\Expr\Cast\Bool $node)
    {
        return $this->handleCastExpression($node, "\\bool");
    }

    public function pprintDoubleCastExpr(Node\Expr\Cast\Double $node)
    {
        return $this->handleCastExpression($node, "float");
    }

    public function pprintIntCastExpr(Node\Expr\Cast\Int $node)
    {
        return $this->handleCastExpression($node, "\\int");
    }

    public function pprintObjectCastExpr(Node\Expr\Cast\Object $node)
    {
        return $this->handleCastExpression($node, "object");
    }

    public function pprintStringCastExpr(Node\Expr\Cast\String $node)
    {
        return $this->handleCastExpression($node, "string");
    }

    public function pprintUnsetCastExpr(Node\Expr\Cast\Unset_ $node)
    {
        return $this->handleCastExpression($node, "unset");
    }

    /**
     * @param Node\Expr\Cast $node
     * @param string $type
     * @return string
     */
    private function handleCastExpression(Node\Expr\Cast $node, $type)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(" . $type . "()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }
    public function pprintClassConstFetchExpr(Node\Expr\ClassConstFetch $node)
    {
        $className = $this->handleNameOrExpression($node->class);

        $constName = $this->pprint($node->name);

        $fragment = "fetchClassConst(" . $className . "," . $constName . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }
    public function pprintCloneExpr(Node\Expr\Clone_ $node)
    {
        $fragment = "clone(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintClosureExpr(Node\Expr\Closure $node)
    {
        $body = array();
        $params = array();
        $uses = array();

        foreach ($node->uses as $use)
            $uses[] = $this->pprint($use);
        foreach ($node->params as $param)
            $params[] = $this->pprint($param);
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $byRef = $node->byRef ? "true" : "false";
        $static = $node->static ? "true" : "false";

        $fragment = "closure([" . implode(",", $body) . "],[";
        $fragment .= implode(",", $params) . "],[";
        $fragment .= implode(",", $uses) . "],";
        $fragment .= $byRef . "," . $static . ")";

        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintClosureUseExpr(Node\Expr\ClosureUse $node)
    {
        $byRef = $node->byRef ? "true" : "false";

        $fragment = "closureUse(" . $this->pprint($node->var) . "," . $byRef . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintConstFetchExpr(Node\Expr\ConstFetch $node)
    {
        $fragment = "fetchConst(" . $this->pprint($node->name) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintEmptyExpr(Node\Expr\Empty_ $node)
    {
        $fragment = "empty(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintErrorSuppressExpr(Node\Expr\ErrorSuppress $node)
    {
        $fragment = "suppress(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }
    public function pprintEvalExpr(Node\Expr\Eval_ $node)
    {
        $fragment = "eval(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintExitExpr(Node\Expr\Exit_ $node)
    {
        $expr = $this->handlePossibleExpression($node->expr);

        $fragment = "exit(" . $expr . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintFuncCallExpr(Node\Expr\FuncCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->handleNameOrExpression($node->name);

        $fragment = "call(" . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIncludeExpr(Node\Expr\Include_ $node)
    {
        $fragment = "include(" . $this->pprint($node->expr) . ",";
        if (Node\Expr\Include_::TYPE_INCLUDE == $node->type)
            $fragment .= "include()";
        elseif (Node\Expr\Include_::TYPE_INCLUDE_ONCE == $node->type)
            $fragment .= "includeOnce()";
        elseif (Node\Expr\Include_::TYPE_REQUIRE == $node->type)
            $fragment .= "require()";
        else
            $fragment .= "requireOnce()";
        $fragment .= ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintInstanceofExpr(Node\Expr\Instanceof_ $node)
    {
        $right = $this->handleNameOrExpression($node->class);
        $left = $this->pprint($node->expr);

        $fragment = "instanceOf(" . $left . "," . $right . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }
    public function pprintIssetExpr(Node\Expr\Isset_ $node)
    {
        $exprs = array();
        foreach ($node->vars as $var)
            $exprs[] = $this->pprint($var);

        $fragment = "isSet([" . implode(",", $exprs) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintListExpr(Node\Expr\List_ $node)
    {
        $exprs = array();
        foreach ($node->vars as $var)
            $exprs[] = $this->handlePossibleExpression($var);

        $fragment = "listExpr([" . implode(",", $exprs) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMethodCallExpr(Node\Expr\MethodCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->handleNameOrExpression($node->name);

        $target = $this->pprint($node->var);

        $fragment = "methodCall(" . $target . "," . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }
    public function pprintNewExpr(Node\Expr\New_ $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->handleNameOrExpression($node->class);

        $fragment = "new(" . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPostDecExpr(Node\Expr\PostDec $node)
    {
        return $this->handleIncreaseDecreaseVarExpression($node, "postDec");
    }

    public function pprintPostIncExpr(Node\Expr\PostInc $node)
    {
        return $this->handleIncreaseDecreaseVarExpression($node, "postInc");
    }

    public function pprintPreDecExpr(Node\Expr\PreDec $node)
    {
        return $this->handleIncreaseDecreaseVarExpression($node, "preDec");
    }

    public function pprintPreIncExpr(Node\Expr\PreInc $node)
    {
        return $this->handleIncreaseDecreaseVarExpression($node, "preInc");
    }

    /**
     * @param Node\Expr\PostDec $node
     * @return string
     */
    private function handleIncreaseDecreaseVarExpression(Node\Expr $node, $operation)
    {
        $this->varIsDecl = true;
        $operand = $this->pprint($node->var);
        $this->varIsDecl = false;
        $fragment = "unaryOperation(" . $operand . ",".$operation."())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPrintExpr(Node\Expr\Print_ $node)
    {
        $operand = $this->pprint($node->expr);
        $fragment = "print(" . $operand . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPropertyFetchExpr(Node\Expr\PropertyFetch $node)
    {
        $name = $this->handleNameOrExpression($node->name);

        $fragment = "propertyFetch(" . $this->pprint($node->var) . "," . $name . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShellExecExpr(Node\Expr\ShellExec $node)
    {
        $parts = array();
        foreach ($node->parts as $item) {
            if ($item instanceof Node\Expr) {
                $parts[] = $this->pprint($item);
            } else {
                $parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
            }
        }

        $fragment = "shellExec([" . implode(",", $parts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticCallExpr(Node\Expr\StaticCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->handleNameOrExpression($node->name);
        $class = $this->handleNameOrExpression($node->class);

        $fragment = "staticCall({$class},{$name},[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticPropertyFetchExpr(Node\Expr\StaticPropertyFetch $node)
    {
        $name = $this->handleNameOrExpression($node->name);
        $class = $this->handleNameOrExpression($node->class);

        $fragment = "staticPropertyFetch({$class},{$name})";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTernaryExpr(Node\Expr\Ternary $node)
    {
        $cond = $this->pprint($node->cond);
        $if = $this->handlePossibleExpression($node->if);
        $else = $this->pprint($node->else);

        $fragment = "ternary({$cond},{$if},{$else})";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintUnaryMinusExpr(Node\Expr\UnaryMinus $node)
    {
        $operand = $this->pprint($node->expr);

        $fragment = "unaryOperation(" . $operand . ",unaryMinus())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintUnaryPlusExpr(Node\Expr\UnaryPlus $node)
    {
        $operand = $this->pprint($node->expr);

        $fragment = "unaryOperation(" . $operand . ",unaryPlus())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintVariableExpr(Node\Expr\Variable $node)
    {
        if ($node->name instanceof Node\Expr) {
            $prevInAssignExpr = $this->varIsDecl;
            $this->varIsDecl = false;
            $name = $this->pprint($node->name);
            $name = "expr({$name})";
            $this->varIsDecl = $prevInAssignExpr;
        } else {
            $name = $this->pprint($node->name);
            $name = "name({$name})";
        }

        $fragment = "var({$name})";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintYieldExpr(Node\Expr\Yield_ $node)
    {
        $valuePart = $this->handlePossibleExpression($node->value);
        $keyPart = $this->handlePossibleExpression($node->key);

        $fragment = "yield({$keyPart},{$valuePart})";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintFullyQualifiedName(Node\Name\FullyQualified $node)
    {
        return $this->pprintName($node, $prefix = "/");
    }

    public function pprintRelativeName(Node\Name\Relative $node)
    {
        return $this->pprintName($node);
    }

    public function pprintName(Node\Name $node, $prefix = "")
    {
        $fragment = $this->implodeName($node);
        $fragment = "name(\"" . $prefix . $fragment . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintParam(Node\Param $node)
    {
        if (null == $node->type) {
            $type = "noName()";
        } else {
            if ($node->type instanceof Node\Name) {
                $type = "someName(" . $this->pprint($node->type) . ")";
            } else {
                $type = "someName(name(\"" . $node->type . "\"))";
            }
        }

        $default = $this->handlePossibleExpression($node->default);
        $byRef = $node->byRef ? "true" : "false";

        $fragment = "param(\"" . $node->name . "\"," . $default . "," . $type . "," . $byRef . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDNumberScalar(Node\Scalar\DNumber $node)
    {
    	if (is_infinite($node->value)) {
	        $fragment = "fetchConst(name(\"INF\"))";
	    } else {
	    	$fragment = "float(" . sprintf('%f', $node->value) . ")";
	        $fragment = "scalar(" . $fragment . ")";
	    }
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEncapsedScalar(Node\Scalar\Encapsed $node)
    {
        $parts = array();
        foreach ($node->parts as $item) {
            if ($item instanceof Node\Expr) {
                $parts[] = $this->pprint($item);
            } else {
                $parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
            }
        }
        $fragment = "scalar(encapsed([" . implode(",", $parts) . "]))";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLNumberScalar(Node\Scalar\LNumber $node)
    {
        $fragment = "integer(" . sprintf('%d', $node->value) . ")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassMagicConstScalar(Node\Scalar\MagicConst\Class_ $node)
    {
        // If we are inside a trait and find __CLASS__, we have no clue what it should
        // be, so leave it unresolved for now; else tag it with the class we are actually
        // inside at the moment.
        if ($this->insideTrait) {
            $fragment = "classConstant()";
        } else {
            $ns = $this->currentNamespace;
            $currentClass = strlen($ns) > 0 ? $ns . "\\" . $this->currentClass : $this->currentClass;
            $currentClass = $this->rascalizeString($currentClass);
            $fragment = "classConstant()[@actualValue=\"{$currentClass}\"]";
        }
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDirMagicConstScalar(Node\Scalar\MagicConst\Dir $node)
    {
        return $this->handleMagicConstExpression($node, "dirConstant", dirname($this->filename));
    }

    public function pprintFileMagicConstScalar(Node\Scalar\MagicConst\File $node)
    {
        return $this->handleMagicConstExpression($node, "fileConstant", $this->filename);
    }

    public function pprintFunctionMagicConstScalar(Node\Scalar\MagicConst\Function_ $node)
    {
        return $this->handleMagicConstExpression($node, "funcConstant", $this->currentFunction);
    }

    public function pprintLineMagicConstScalar(Node\Scalar\MagicConst\Line $node)
    {
        return $this->handleMagicConstExpression($node, "lineConstant", $node->getLine());
    }

    public function pprintMethodMagicConstScalar(Node\Scalar\MagicConst\Method $node)
    {
        return $this->handleMagicConstExpression($node, "methodConstant",
            $this->currentMethod ? $this->currentClass . "::" . $this->currentMethod : "");
    }

    public function pprintNamespaceMagicConstScalar(Node\Scalar\MagicConst\Namespace_ $node)
    {
        return $this->handleMagicConstExpression($node, "namespaceConstant", $this->currentNamespace);
    }

    public function pprintTraitMagicConstScalar(Node\Scalar\MagicConst\Trait_ $node)
    {
        return $this->handleMagicConstExpression($node, "traitConstant", $this->currentTrait);
    }

    /**
     * @param Node\Scalar\MagicConst $node
     * @param $name
     * @param $value
     * @return string
     */
    private function handleMagicConstExpression(Node\Scalar\MagicConst $node, $name, $value)
    {
        $fragment = "{$name}()[@actualValue=\"{$value}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStringScalar(Node\Scalar\String $node)
    {
        $fragment = "string(\"" . $this->rascalizeString($node->value) . "\")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBreakStmt(Node\Stmt\Break_ $node)
    {
        $num = $this->handlePossibleExpression($node->num);

        $fragment = "\\break(" . $num . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintCaseStmt(Node\Stmt\Case_ $node)
    {
        $cond = $this->handlePossibleExpression($node->cond);

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "\\case(" . $cond . ",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintCatchStmt(Node\Stmt\Catch_ $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $xtype = $this->pprint($node->type);
        $xname = $this->pprint($node->var);

        $fragment = "\\catch(" . $xtype . "," . $xname . ",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassStmt(Node\Stmt\Class_ $node)
    {
        $priorClass = $this->currentClass;
        $this->currentClass = $node->name;

        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $implements = array();
        foreach ($node->implements as $implemented)
            $implements[] = $this->pprint($implemented);

        $extends = $this->handlePossibleName($node->extends);

        $modifiers = array();
        if ($node->type & Node\Stmt\Class_::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_FINAL)
            $modifiers[] = "final()";

        $fragment = "class(\"" . $node->name . "\",{" . implode(",", $modifiers) . "}," . $extends . ",";
        $fragment .= "[" . implode(",", $implements) . "],[";
        $fragment .= implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        $fragment = "classDef(" . $fragment . ")";
        $priorDecl = $this->addDeclarations;
        $this->addDeclarations = false;
        $fragment .= $this->annotateASTNode($node);
        $this->addDeclarations =  $priorDecl;

        $this->currentClass = $priorClass;

        return $fragment;
    }

    public function pprintClassConstStmt(Node\Stmt\ClassConst $node)
    {
        $consts = array();
        foreach ($node->consts as $const)
            $consts[] = $this->pprint($const);

        $fragment = "constCI([" . implode(",", $consts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassMethodStmt(Node\Stmt\ClassMethod $node)
    {
        $priorMethod = $this->currentMethod;
        $this->currentMethod = $node->name;

        $priorInsideFunction = $this->insideFunction;
        $this->insideFunction = false;

        $body = array();
        if (null != $node->stmts)
            foreach ($node->stmts as $thestmt)
                $body[] = $this->pprint($thestmt);

        $params = array();
        foreach ($node->params as $param)
            $params[] = $this->pprint($param);

        $modifiers = array();
        if ($node->type & Node\Stmt\Class_::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_STATIC)
            $modifiers[] = "static()";

        $byRef = $node->byRef ? "true" : "false";

        $fragment = "method(\"" . $node->name . "\",{" . implode(",", $modifiers) . "}," . $byRef . ",[" . implode(",", $params) . "],[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        $this->currentMethod = $priorMethod;
        $this->insideFunction = $priorInsideFunction;

        return $fragment;
    }

    public function pprintConstStmt(Node\Stmt\Const_ $node)
    {
        $consts = array();
        foreach ($node->consts as $const)
            $consts[] = $this->pprint($const);

        $fragment = "const([" . implode(",", $consts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintContinueStmt(Node\Stmt\Continue_ $node)
    {
        $num = $this->handlePossibleExpression($node->num);

        $fragment = "\\continue(" . $num . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDeclareStmt(Node\Stmt\Declare_ $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $decls = array();
        foreach ($node->declares as $decl)
            $decls[] = $this->pprint($decl);

        $fragment = "declare([" . implode(",", $decls) . "],[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDeclareDeclareStmt(Node\Stmt\DeclareDeclare $node)
    {
        $fragment = "declaration(\"" . $node->key . "\", " . $this->pprint($node->value) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDoStmt(Node\Stmt\Do_ $node)
    {
        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $fragment = "\\do(" . $this->pprint($node->cond) . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEchoStmt(Node\Stmt\Echo_ $node)
    {
        $parts = array();
        foreach ($node->exprs as $expr)
            $parts[] = $this->pprint($expr);

        $fragment = "echo([" . implode(",", $parts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintElseStmt(Node\Stmt\Else_ $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "\\else([" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintElseIfStmt(Node\Stmt\ElseIf_ $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "elseIf(" . $this->pprint($node->cond) . ",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintExprStmt(Node\Stmt\Expr $node)
    {
        $fragment = "exprstmt(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintForStmt(Node\Stmt\For_ $node)
    {
        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $loops = array();
        foreach ($node->loop as $loop)
            $loops[] = $this->pprint($loop);

        $conds = array();
        foreach ($node->cond as $cond)
            $conds[] = $this->pprint($cond);

        $inits = array();
        foreach ($node->init as $init)
            $inits[] = $this->pprint($init);

        $fragment = "\\for([" . implode(",", $inits) . "],[" . implode(",", $conds) . "],[" . implode(",", $loops) . "],[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintForeachStmt(Node\Stmt\Foreach_ $node)
    {
        $valueVar = $this->pprint($node->valueVar);
        $expr = $this->pprint($node->expr);
        $byRef = $node->byRef ? "true" : "false";

        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $keyVar = $this->handlePossibleExpression($node->keyVar);

        $fragment = "foreach(" . $expr . "," . $keyVar . "," . $byRef . "," . $valueVar . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintFunctionStmt(Node\Stmt\Function_ $node)
    {
        $priorFunction = $this->currentFunction;
        $this->currentFunction = $node->name;

        $priorInsideFunction = $this->insideFunction;
        $this->insideFunction = true;

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $params = array();
        foreach ($node->params as $param)
            $params[] = $this->pprint($param);

        $byRef = $node->byRef ? "true" : "false";

        $fragment = "function(\"" . $node->name . "\"," . $byRef . ",[" . implode(",", $params) . "],[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        $this->currentFunction = $priorFunction;
        $this->insideFunction = $priorInsideFunction;

        return $fragment;
    }

    public function pprintGlobalStmt(Node\Stmt\Global_ $node)
    {
        $vars = array();
        foreach ($node->vars as $var)
            $vars[] = $this->pprint($var);

        $fragment = "global([" . implode(",", $vars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintGotoStmt(Node\Stmt\Goto_ $node)
    {
        $fragment = "goto(" . $this->pprint($node->name) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintHaltCompilerStmt(Node\Stmt\HaltCompiler $node)
    {
        $fragment = "haltCompiler(\"" . $this->rascalizeString($node->remaining) . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIfStmt(Node\Stmt\If_ $node)
    {
        $cond = $this->pprint($node->cond);

        if (null != $node->else)
            $elseNode = "someElse(" . $this->pprint($node->else) . ")";
        else
            $elseNode = "noElse()";

        $elseIfs = array();
        foreach ($node->elseifs as $elseif)
            $elseIfs[] = $this->pprint($elseif);

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "\\if(" . $cond . ",[" . implode(",", $body) . "],[" . implode(",", $elseIfs) . "]," . $elseNode . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintInlineHTMLStmt(Node\Stmt\InlineHTML $node)
    {
        $fragment = "inlineHTML(\"" . $this->rascalizeString($node->value) . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintInterfaceStmt(Node\Stmt\Interface_ $node)
    {
        $priorInterface = $this->currentInterface;
        $this->currentInterface = $node->name;

        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $extends = array();
        foreach ($node->extends as $extended)
            $extends[] = $this->pprint($extended);

        $fragment = "interface(\"" . $node->name . "\",[";
        $fragment .= implode(",", $extends) . "],[";
        $fragment .= implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        $fragment = "interfaceDef(" . $fragment . ")";
        $priorDecl = $this->addDeclarations;
        $this->addDeclarations = false;
        $fragment .= $this->annotateASTNode($node);
        $this->addDeclarations =  $priorDecl;

        $this->currentInterface = $priorInterface;

        return $fragment;
    }

    public function pprintLabelStmt(Node\Stmt\Label $node)
    {
        $fragment = "label(\"" . $node->name . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNamespaceStmt(Node\Stmt\Namespace_ $node)
    {
// If we have a non-null name, set this to the namespace name; if we
// don't, this is a global namespace declaration, like
// namespace { global stuff }
        $priorNamespace = $this->currentNamespace;
        if (null != $node->name)
            $this->currentNamespace = $this->implodeName($node->name);
        else
            $this->currentNamespace = "";

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

// Again check the name -- since it is optional, we return an OptionName
// here, which could be noName() if this is a global namespace
        $name = $this->handlePossibleName($node->name);

// The third option shouldn't occur, but is put in just in case; the first
// option is the case where we have a body, the second is where we have
// a namespace header, like namespace DB; that opens a new block but doesn't
// enclose it in braces
        if (null != $node->stmts)
            $fragment = "namespace(" . $name . ",[" . implode(",", $body) . "])";
        else
            if (null != $node->name)
                $fragment = "namespaceHeader({$this->pprint($node->name)})";
            else
                $fragment = "namespaceHeader(noName())";

        $fragment .= $this->annotateASTNode($node);

// If we had a statement body, then we reset the namespace at the end; if
// we didn't, it means that we just had a namespace declaration like
// namespace DB; which had no body, but then is still active at the end
// in which case we don't want to reset it
        if (null != $node->stmts)
            $this->currentNamespace = $priorNamespace;

        return $fragment;
    }

    public function pprintPropertyStmt(Node\Stmt\Property $node)
    {
        $props = array();
        foreach ($node->props as $prop)
            $props[] = $this->pprint($prop);

        $modifiers = array();
        if ($node->type & Node\Stmt\Class_::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & Node\Stmt\Class_::MODIFIER_STATIC)
            $modifiers[] = "static()";

        $fragment = "property({" . implode(",", $modifiers) . "},[" . implode(",", $props) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPropertyPropertyStmt(Node\Stmt\PropertyProperty $node)
    {
        $default = $this->handlePossibleExpression($node->default);

        $fragment = "property(\"" . $node->name . "\"," . $default . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintReturnStmt(Node\Stmt\Return_ $node)
    {
        $expr = $this->handlePossibleExpression($node->expr);

        $fragment = "\\return(" . $expr . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticStmt(Node\Stmt\Static_ $node)
    {
        $staticVars = array();
        foreach ($node->vars as $var)
            $staticVars[] = $this->pprint($var);

        $fragment = "static([" . implode(",", $staticVars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticVarStmt(Node\Stmt\StaticVar $node)
    {
        $default = $this->handlePossibleExpression($node->default);

        $fragment = "staticVar(\"" . $node->name . "\"," . $default . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintSwitchStmt(Node\Stmt\Switch_ $node)
    {
        $cases = array();
        foreach ($node->cases as $case)
            $cases[] = $this->pprint($case);

        $fragment = "\\switch(" . $this->pprint($node->cond) . ",[" . implode(",", $cases) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintThrowStmt(Node\Stmt\Throw_ $node)
    {
        $fragment = "\\throw(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTraitStmt(Node\Stmt\Trait_ $node)
    {
        $body = array();

        $priorTrait = $this->currentTrait;
        $this->currentTrait = $node->name;
        $this->insideTrait = true;

        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "trait(\"" . $node->name . "\",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        $fragment = "traitDef(" . $fragment . ")";
        $priorDecl = $this->addDeclarations;
        $this->addDeclarations = false;
        $fragment .= $this->annotateASTNode($node);
        $this->addDeclarations =  $priorDecl;

        $this->currentTrait = $priorTrait;
        $this->insideTrait = false;

        return $fragment;
    }

    public function pprintTraitUseStmt(Node\Stmt\TraitUse $node)
    {
        $adaptations = array();
        foreach ($node->adaptations as $adaptation)
            $adaptations[] = $this->pprint($adaptation);

        $traits = array();
        foreach ($node->traits as $trait)
            $traits[] = $this->pprint($trait);

        $fragment = "traitUse([" . implode(",", $traits) . "],[" . implode(",", $adaptations) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAliasTraitUseAdaptationStmt(Node\Stmt\TraitUseAdaptation\Alias $node)
    {
        $newName = $this->handlePossibleName($node->newName);

        if (null != $node->newModifier) {
            $modifiers = array();
            if ($node->newModifier & Node\Stmt\Class_::MODIFIER_PUBLIC)
                $modifiers[] = "\\public()";
            if ($node->newModifier & Node\Stmt\Class_::MODIFIER_PROTECTED)
                $modifiers[] = "protected()";
            if ($node->newModifier & Node\Stmt\Class_::MODIFIER_PRIVATE)
                $modifiers[] = "\\private()";
            if ($node->newModifier & Node\Stmt\Class_::MODIFIER_ABSTRACT)
                $modifiers[] = "abstract()";
            if ($node->newModifier & Node\Stmt\Class_::MODIFIER_FINAL)
                $modifiers[] = "final()";
            if ($node->newModifier & Node\Stmt\Class_::MODIFIER_STATIC)
                $modifiers[] = "static()";
            $newModifier = "{ " . implode(",", $modifiers) . " }";
        } else {
            $newModifier = "{ }";
        }

        $newMethod = $this->pprint($node->method);
        $trait = $this->handlePossibleName($node->trait);

        $fragment = "traitAlias(" . $trait . "," . $newMethod . "," . $newModifier . "," . $newName . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPrecedenceTraitUseAdaptationStmt(Node\Stmt\TraitUseAdaptation\Precedence $node)
    {
        $insteadOf = array();
        foreach ($node->insteadof as $item)
            $insteadOf[] = $this->pprint($item);

        $trait = $this->handlePossibleName($node->trait);
        $method = $this->pprint($node->method);

        $fragment = "traitPrecedence(" . $trait . "," . $method . ",{" . implode(",", $insteadOf) . "})";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTryCatchStmt(Node\Stmt\TryCatch $node)
    {
        $finallyBody = array();
        if (null != $node->finallyStmts)
            foreach ($node->finallyStmts as $fstmt)
                $finallyBody[] = $this->pprint($fstmt);

        $catches = array();
        foreach ($node->catches as $toCatch)
            $catches[] = $this->pprint($toCatch);

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        if (null !== $node->finallyStmts)
            $fragment = "tryCatchFinally([" . implode(",", $body) . "],[" . implode(",", $catches) . "],[" . implode(",", $finallyBody) . "])";
        else
            $fragment = "tryCatch([" . implode(",", $body) . "],[" . implode(",", $catches) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }
    public function pprintUnsetStmt(Node\Stmt\Unset_ $node)
    {
        $vars = array();
        foreach ($node->vars as $var)
            $vars[] = $this->pprint($var);

        $fragment = "unset([" . implode(",", $vars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUseStmt(Node\Stmt\Use_ $node)
    {
        $uses = array();
        foreach ($node->uses as $use)
            $uses[] = $this->pprint($use);

        $fragment = "use([" . implode(",", $uses) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }


    public function pprintUseUseStmt(Node\Stmt\UseUse $node)
    {
        $name = $this->pprint($node->name);
        $alias = $this->handlePossibleName($node->alias);

        $fragment = "use(" . $name . "," . $alias . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintWhileStmt(Node\Stmt\While_ $node)
    {
        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $fragment = "\\while(" . $this->pprint($node->cond) . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateAstNode($node);

        return $fragment;
    }
    /**
     * @param string|Node\Name $node
     * @return string
     */
    public function implodeName($node)
    {
        if (is_string($node))
            $fragment = $node;
        else if (is_array($node->parts))
            $fragment = implode("/", $node->parts);
        else
            $fragment = $node->parts;

        return $fragment;
    }

    /**
     * @param Node\Expr|Node\Name $node
     * @throws Exception
     * @return string
     */
    private function handleNameOrExpression(Node $node)
    {
        $name = $this->pprint($node);
        if ($node instanceof Node\Expr) {
            return "expr({$name})" . $this->annotateASTNode($node);
        } else if ($node instanceof Node\Name) {
            return "name({$name})" . $this->annotateASTNode($node);
        }

        throw new Exception("Node " . get_class($node) . " not supported. " . __METHOD__ . "::" . __LINE__);
    }

    /**
     * @param Node\Expr|null $node
     * @throws Exception
     * @return string
     */
    private function handlePossibleExpression($node)
    {
        if (is_null($node)) {
            return "noExpr()";
        } else if ($node instanceOf Node\Expr) {
            $value = $this->pprint($node);
            return "someExpr({$value})" . $this->annotateASTNode($node);
        }

        throw new Exception("Invalid input, must be Expr or null. " . __METHOD__ . "::" . __LINE__);
    }

    /**
     * @param Node\Name|null $node
     * @throws Exception
     * @return string
     */
    private function handlePossibleName($node)
    {
        if (is_null($node)) {
            return "noName()";
        } else if ($node instanceOf Node\Name) {
            $value = $this->pprint($node);
            return "someName({$value})";
        }

        throw new Exception("Invalid input: must be Name or null. " . __METHOD__ . "::" . __LINE__);
    }
}