<?php

namespace Rascal;

class AST2Rascal extends BasePrinter
{

    private $filename = "";

    private $addLocations = false;

    private $relativeLocations = false;

    private $addIds = false;

    private $addPHPDocs = false;

    private $idPrefix = "";

    private $insideTrait = false;

    private $currentFunction = "";

    private $currentClass = "";

    private $currentTrait = "";

    private $currentMethod = "";

    private $currentNamespace = "";

    /**
     *
     * @param string $str
     * @param bool $locs
     * @param bool $rel
     * @param bool $ids
     * @param string $prefix
     * @param bool $docs
     */
    public function __construct($str, $locs, $rel, $ids, $prefix, $docs)
    {
        $this->filename = $str;
        $this->addLocations = $locs;
        $this->relativeLocations = $rel;
        $this->addIds = $ids;
        $this->idPrefix = $prefix;
        $this->addPHPDocs = $docs;
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

    private function addLocationTag(\PhpParser\Node $node)
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
     * @return string
     */
    private function addPHPDocForNode(\PhpParser\Node $node)
    {
        $docString = "@phpdoc=\"%s\"";
        if ($doc = $node->getDocComment())
            return sprintf($docString, $this->rascalizeString($doc));
        return sprintf($docString, null);
    }

    private function annotateASTNode(\PhpParser\Node $node)
    {
        $tagsToAdd = array();
        if ($this->addLocations)
            $tagsToAdd[] = $this->addLocationTag($node);
        if ($this->addIds)
            $tagsToAdd[] = $this->addUniqueId();
        if ($this->addPHPDocs)
            $tagsToAdd[] = $this->addPHPDocForNode($node);

        if (count($tagsToAdd) > 0)
            return "[" . implode(",", $tagsToAdd) . "]";
        return "";
    }

    public function pprintArg(\PhpParser\Node\Arg $node)
    {
        $argValue = $this->pprint($node->value);

        if ($node->byRef)
            $byRef = "true";
        else
            $byRef = "false";

        $fragment = "actualParameter(" . $argValue . "," . $byRef . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintConst(\PhpParser\Node\Const_ $node)
    {
        $fragment = "const(\"" . $node->name . "\"," . $this->pprint($node->value) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayExpr(\PhpParser\Node\Expr\Array_ $node)
    {
        $items = array();
        foreach ($node->items as $item)
            $items[] = $this->pprint($item);

        $fragment = "array([" . implode(",", $items) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayDimFetchExpr(\PhpParser\Node\Expr\ArrayDimFetch $node)
    {
        if (null != $node->dim)
            $dim = "someExpr(" . $this->pprint($node->dim) . ")";
        else
            $dim = "noExpr()";

        $fragment = "fetchArrayDim(" . $this->pprint($node->var) . "," . $dim . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayItemExpr(\PhpParser\Node\Expr\ArrayItem $node)
    {
        $nodeValue = $this->pprint($node->value);

        if (null == $node->key)
            $key = "noExpr()";
        else
            $key = "someExpr(" . $this->pprint($node->key) . ")";

        if ($node->byRef)
            $byRef = "true";
        else
            $byRef = "false";

        $fragment = "arrayElement(" . $key . "," . $nodeValue . "," . $byRef . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignExpr(\PhpParser\Node\Expr\Assign $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assign(" . $assignVar . "," . $assignExpr . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseAndAssignOpExpr(\PhpParser\Node\Expr\AssignOp\BitwiseAnd $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",bitwiseAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseOrAssignOpExpr(\PhpParser\Node\Expr\AssignOp\BitwiseOr $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",bitwiseOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseXorAssignOpExpr(\PhpParser\Node\Expr\AssignOp\BitwiseXor $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",bitwiseXor())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintConcatAssignOpExpr(\PhpParser\Node\Expr\AssignOp\Concat $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",concat())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDivAssignOpExpr(\PhpParser\Node\Expr\AssignOp\Div $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",div())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMinusAssignOpExpr(\PhpParser\Node\Expr\AssignOp\Minus $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",minus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintModAssignOpExpr(\PhpParser\Node\Expr\AssignOp\Mod $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",\\mod())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMulAssignOpExpr(\PhpParser\Node\Expr\AssignOp\Mul $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",mul())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPlusAssignOpExpr(\PhpParser\Node\Expr\AssignOp\Plus $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",plus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShiftLeftAssignOpExpr(\PhpParser\Node\Expr\AssignOp\ShiftLeft $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",leftShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShiftRightAssignOpExpr(\PhpParser\Node\Expr\AssignOp\ShiftRight $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",rightShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignRefExpr(\PhpParser\Node\Expr\AssignRef $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "refAssign(" . $assignVar . "," . $assignExpr . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseAndBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\BitwiseAnd $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",bitwiseAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseNotExpr(\PhpParser\Node\Expr\BitwiseNot $node)
    {
        $expr = $this->pprint($node->expr);

        $fragment = "unaryOperation(" . $expr . ",bitwiseNot())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseOrBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\BitwiseOr $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",bitwiseOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseXorBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\BitwiseXor $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",bitwiseXor())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBooleanAndBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\BooleanAnd $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",booleanAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBooleanNotExpr(\PhpParser\Node\Expr\BooleanNot $node)
    {
        $expr = $this->pprint($node->expr);

        $fragment = "unaryOperation(" . $expr . ",booleanNot())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBooleanOrBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\BooleanOr $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",booleanOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayCastExpr(\PhpParser\Node\Expr\Cast\Array_ $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(array()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintBoolCastExpr(\PhpParser\Node\Expr\Cast\Bool $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(\\bool()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintDoubleCastExpr(\PhpParser\Node\Expr\Cast\Double $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(float()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintIntCastExpr(\PhpParser\Node\Expr\Cast\Int $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(\\int()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintObjectCastExpr(\PhpParser\Node\Expr\Cast\Object $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(object()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintStringCastExpr(\PhpParser\Node\Expr\Cast\String $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(string()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintUnsetCastExpr(\PhpParser\Node\Expr\Cast\Unset_ $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(unset()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintClassConstFetchExpr(\PhpParser\Node\Expr\ClassConstFetch $node)
    {
        $name = $this->pprint($node->class);
        if ($node->class instanceof \PhpParser\Node\Name)
            $name = "name({$name})";
        else
            $name = "expr({$name})";

        $fragment = "fetchClassConst(" . $name . ",\"" . $node->name . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintCloneExpr(\PhpParser\Node\Expr\Clone_ $node)
    {
        $fragment = "clone(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintClosureExpr(\PhpParser\Node\Expr\Closure $node)
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

        $fragment = "closure([" . implode(",", $body) . "],[";
        $fragment .= implode(",", $params) . "],[";
        $fragment .= implode(",", $uses) . "],";
        if ($node->byRef)
            $fragment .= "true,";
        else
            $fragment .= "false,";
        if ($node->static)
            $fragment .= "true";
        else
            $fragment .= "false";
        $fragment .= ")";

        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintClosureUseExpr(\PhpParser\Node\Expr\ClosureUse $node)
    {
        $fragment = "closureUse(\"" . $node->var . "\",";
        if ($node->byRef)
            $fragment .= "true";
        else
            $fragment .= "false";
        $fragment .= ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintConcatBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Concat $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",concat())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintConstFetchExpr(\PhpParser\Node\Expr\ConstFetch $node)
    {
        $fragment = "fetchConst(" . $this->pprint($node->name) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintDivBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Div $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",div())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEmptyExpr(\PhpParser\Node\Expr\Empty_ $node)
    {
        $fragment = "empty(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintEqualBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Equal $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",equal())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintErrorSuppressExpr(\PhpParser\Node\Expr\ErrorSuppress $node)
    {
        $fragment = "suppress(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintEvalExpr(\PhpParser\Node\Expr\Eval_ $node)
    {
        $fragment = "eval(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintExitExpr(\PhpParser\Node\Expr\Exit_ $node)
    {
        if (null != $node->expr)
            $fragment = "someExpr(" . $this->pprint($node->expr) . ")";
        else
            $fragment = "noExpr()";
        $fragment = "exit(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintFuncCallExpr(\PhpParser\Node\Expr\FuncCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->pprint($node->name);
        if ($node->name instanceof \PhpParser\Node\Name)
            $name = "name({$name})";
        else
            $name = "expr({$name})";

        $fragment = "call(" . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintGreaterBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Greater $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",gt())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintGreaterOrEqualBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",geq())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIdenticalBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Identical $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",identical())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIncludeExpr(\PhpParser\Node\Expr\Include_ $node)
    {
        $fragment = "include(" . $this->pprint($node->expr) . ",";
        if (\PhpParser\Node\Expr\Include_::TYPE_INCLUDE == $node->type)
            $fragment .= "include()";
        elseif (\PhpParser\Node\Expr\Include_::TYPE_INCLUDE_ONCE == $node->type)
            $fragment .= "includeOnce()";
        elseif (\PhpParser\Node\Expr\Include_::TYPE_REQUIRE == $node->type)
            $fragment .= "require()";
        else
            $fragment .= "requireOnce()";
        $fragment .= ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintInstanceofExpr(\PhpParser\Node\Expr\Instanceof_ $node)
    {
        $right = $this->pprint($node->class);
        if ($node->class instanceof \PhpParser\Node\Name)
            $right = "name({$right})";
        else
            $right = "expr({$right})";

        $left = $this->pprint($node->expr);

        $fragment = "instanceOf(" . $left . "," . $right . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIssetExpr(\PhpParser\Node\Expr\Isset_ $node)
    {
        $exprs = array();
        foreach ($node->vars as $var)
            $exprs[] = $this->pprint($var);

        $fragment = "isSet([" . implode(",", $exprs) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintListExpr(\PhpParser\Node\Expr\List_ $node)
    {
        $exprs = array();
        foreach ($node->vars as $var)
            if (null != $var)
                $exprs[] = "someExpr(" . $this->pprint($var) . ")";
            else
                $exprs[] = "noExpr()";

        $fragment = "listExpr([" . implode(",", $exprs) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLogicalAndBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\LogicalAnd $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",logicalAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLogicalOrBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\LogicalOr $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",logicalOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLogicalXorBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\LogicalXor $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",logicalXor())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMethodCallExpr(\PhpParser\Node\Expr\MethodCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        if ($node->name instanceof \PhpParser\Node\Expr) {
            $name = $this->pprint($node->name);
            $name = "expr({$name})";
        } else {
            $name = "name(name(\"" . $node->name . "\"))";
        }

        $target = $this->pprint($node->var);

        $fragment = "methodCall(" . $target . "," . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMinusBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Minus $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",minus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintModBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Mod $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",\\mod())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMulBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Mul $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",mul())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNewExpr(\PhpParser\Node\Expr\New_ $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->pprint($node->class);

        if ($node->class instanceof \PhpParser\Node\Expr)
            $name = "expr({$name})";
        else
            $name = "name({$name})";

        $fragment = "new(" . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNotEqualBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\NotEqual $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",notEqual())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNotIdenticalBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\NotIdentical $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",notIdentical())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPlusBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Plus $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",plus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPostDecExpr(\PhpParser\Node\Expr\PostDec $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",postDec())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPostIncExpr(\PhpParser\Node\Expr\PostInc $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",postInc())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPreDecExpr(\PhpParser\Node\Expr\PreDec $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",preDec())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPreIncExpr(\PhpParser\Node\Expr\PreInc $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",preInc())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPrintExpr(\PhpParser\Node\Expr\Print_ $node)
    {
        $operand = $this->pprint($node->expr);
        $fragment = "print(" . $operand . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPropertyFetchExpr(\PhpParser\Node\Expr\PropertyFetch $node)
    {
        if ($node->name instanceof \PhpParser\Node\Expr) {
            $fragment = "expr(" . $this->pprint($node->name) . ")";
        } else {
            $fragment = "name(name(\"" . $node->name . "\"))";
        }

        $fragment = "propertyFetch(" . $this->pprint($node->var) . "," . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShellExecExpr(\PhpParser\Node\Expr\ShellExec $node)
    {
        $parts = array();
        foreach ($node->parts as $item) {
            if ($item instanceof \PhpParser\Node\Expr) {
                $parts[] = $this->pprint($item);
            } else {
                $parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
            }
        }

        $fragment = "shellExec([" . implode(",", $parts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShiftLeftBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\ShiftLeft $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",leftShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShiftRightBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\ShiftRight $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",rightShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintSmallerBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\Smaller $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",lt())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintSmallerOrEqualBinaryOpExpr(\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",leq())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticCallExpr(\PhpParser\Node\Expr\StaticCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        if ($node->name instanceof \PhpParser\Node\Expr)
            $name = "expr(" . $this->pprint($node->name) . ")";
        else
            $name = "name(name(\"" . $node->name . "\"))";

        if ($node->class instanceof \PhpParser\Node\Expr) {
            $class = "expr(" . $this->pprint($node->class) . ")";
        } else {
            $class = "name(" . $this->pprint($node->class) . ")";
        }

        $fragment = "staticCall({$class},{$name},[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticPropertyFetchExpr(\PhpParser\Node\Expr\StaticPropertyFetch $node)
    {
        if ($node->name instanceof \PhpParser\Node\Expr) {
            $name = "expr(" . $this->pprint($node->name) . ")";
        } else {
            $name = "name(name(\"" . $node->name . "\"))";
        }

        if ($node->class instanceof \PhpParser\Node\Expr) {
            $class = "expr(" . $this->pprint($node->class) . ")";
        } else {
            $class = "name(" . $this->pprint($node->class) . ")";
        }

        $fragment = "staticPropertyFetch({$class},{$name})";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTernaryExpr(\PhpParser\Node\Expr\Ternary $node)
    {
        $else = $this->pprint($node->else);
        if (null != $node->if)
            $if = "someExpr(" . $this->pprint($node->if) . ")";
        else
            $if = "noExpr()";
        $cond = $this->pprint($node->cond);

        $fragment = "ternary({$cond},{$if},{$else})";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUnaryMinusExpr(\PhpParser\Node\Expr\UnaryMinus $node)
    {
        $operand = $this->pprint($node->expr);
        $fragment = "unaryOperation(" . $operand . ",unaryMinus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUnaryPlusExpr(\PhpParser\Node\Expr\UnaryPlus $node)
    {
        $operand = $this->pprint($node->expr);
        $fragment = "unaryOperation(" . $operand . ",unaryPlus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintVariableExpr(\PhpParser\Node\Expr\Variable $node)
    {
        if ($node->name instanceof \PhpParser\Node\Expr) {
            $fragment = "expr(" . $this->pprint($node->name) . ")";
        } else {
            $fragment = "name(name(\"" . $node->name . "\"))";
        }
        $fragment = "var(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintYieldExpr(\PhpParser\Node\Expr\Yield_ $node)
    {
        if (null != $node->value)
            $valuePart = "someExpr(" . $this->pprint($node->value) . ")";
        else
            $valuePart = "noExpr()";

        if (null != $node->key)
            $keyPart = "someExpr(" . $this->pprint($node->key) . ")";
        else
            $keyPart = "noExpr()";

        $fragment = "yield({$keyPart},{$valuePart})";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintFullyQualifiedName(\PhpParser\Node\Name\FullyQualified $node)
    {
        return $this->pprintName($node);
    }

    public function pprintRelativeName(\PhpParser\Node\Name\Relative $node)
    {
        return $this->pprintName($node);
    }

    public function pprintName(\PhpParser\Node\Name $node)
    {
        if (is_array($node->parts))
            $fragment = implode("::", $node->parts);
        else
            $fragment = $node->parts;
        $fragment = "name(\"" . $fragment . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintParam(\PhpParser\Node\Param $node)
    {
        if (null == $node->type) {
            $type = "noName()";
        } else {
            if ($node->type instanceof \PhpParser\Node\Name) {
                $type = "someName(" . $this->pprint($node->type) . ")";
            } else {
                $type = "someName(name(\"" . $node->type . "\"))";
            }
        }

        if (null == $node->default) {
            $default = "noExpr()";
        } else {
            $default = "someExpr(" . $this->pprint($node->default) . ")";
        }

        $fragment = "param(\"" . $node->name . "\"," . $default . "," . $type . ",";
        if (false == $node->byRef)
            $fragment .= "false";
        else
            $fragment .= "true";
        $fragment .= ")";

        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDNumberScalar(\PhpParser\Node\Scalar\DNumber $node)
    {
        $fragment = "float(" . sprintf('%f', $node->value) . ")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEncapsedScalar(\PhpParser\Node\Scalar\Encapsed $node)
    {
        $parts = array();
        foreach ($node->parts as $item) {
            if ($item instanceof \PhpParser\Node\Expr) {
                $parts[] = $this->pprint($item);
            } else {
                $parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
            }
        }
        $fragment = "scalar(encapsed([" . implode(",", $parts) . "]))";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLNumberScalar(\PhpParser\Node\Scalar\LNumber $node)
    {
        $fragment = "integer(" . sprintf('%d', $node->value) . ")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\Class_ $node)
    {
        // If we are inside a trait and find __CLASS__, we have no clue what it should
        // be, so leave it unresolved for now; else tag it with the class we are actually
        // inside at the moment.
        if ($this->insideTrait) {
            $fragment = "classConstant()";
        } else {
            $fragment = "classConstant()[@actualValue=\"{$this->currentClass}\"]";
        }
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDirMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\Dir $node)
    {
        $fragment = "dirConstant()[@actualValue=\"{dirname($this->filename)}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintFileMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\File $node)
    {
        $fragment = "fileConstant()[@actualValue=\"{$this->filename}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintFunctionMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\Function_ $node)
    {
        $fragment = "funcConstant()[@actualValue=\"{$this->currentFunction}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLineMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\Line $node)
    {
        $fragment = "lineConstant()[@actualValue=\"{$node->getLine()}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMethodMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\Method $node)
    {
        $fragment = "methodConstant()[@actualValue=\"{$this->currentMethod}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNamespaceMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\Namespace_ $node)
    {
        $fragment = "namespaceConstant()[@actualValue=\"{$this->currentNamespace}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTraitMagicConstScalar(\PhpParser\Node\Scalar\MagicConst\Trait_ $node)
    {
        $fragment = "traitConstant()[@actualValue=\"{$this->currentTrait}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStringScalar(\PhpParser\Node\Scalar\String $node)
    {
        $fragment = "string(\"" . $this->rascalizeString($node->value) . "\")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBreakStmt(\PhpParser\Node\Stmt\Break_ $node)
    {
        if (null != $node->num)
            $fragment = "someExpr(" . $this->pprint($node->num) . ")";
        else
            $fragment = "noExpr()";

        $fragment = "\\break(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintCaseStmt(\PhpParser\Node\Stmt\Case_ $node)
    {
        if (null != $node->cond)
            $cond = "someExpr(" . $this->pprint($node->cond) . ")";
        else
            $cond = "noExpr()";

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "\\case(" . $cond . ",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintCatchStmt(\PhpParser\Node\Stmt\Catch_ $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $xtype = $this->pprint($node->type);

        $fragment = "\\catch(" . $xtype . ",\"" . $node->var . "\",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassStmt(\PhpParser\Node\Stmt\Class_ $node)
    {
        $priorClass = $this->currentClass;
        if (strlen($this->currentNamespace) > 0)
            $this->currentClass = $this->currentNamespace . "\\" . $node->name;
        else
            $this->currentClass = $node->name;

        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $implements = array();
        foreach ($node->implements as $implemented)
            $implements[] = $this->pprint($implemented);

        if (null != $node->extends)
            $extends = "someName(" . $this->pprint($node->extends) . ")";
        else
            $extends = "noName()";

        $modifiers = array();
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_STATIC)
            $modifiers[] = "static()";

        $fragment = "class(\"" . $node->name . "\",{" . implode(",", $modifiers) . "}," . $extends . ",";
        $fragment .= "[" . implode(",", $implements) . "],[";
        $fragment .= implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        $fragment = "classDef(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        $this->currentClass = $priorClass;

        return $fragment;
    }

    public function pprintClassConstStmt(\PhpParser\Node\Stmt\ClassConst $node)
    {
        $consts = array();
        foreach ($node->consts as $const)
            $consts[] = $this->pprint($const);

        $fragment = "constCI([" . implode(",", $consts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassMethodStmt(\PhpParser\Node\Stmt\ClassMethod $node)
    {
        $priorMethod = $this->currentMethod;
        $this->currentMethod = $node->name;

        $body = array();
        if (null != $node->stmts)
            foreach ($node->stmts as $thestmt)
                $body[] = $this->pprint($thestmt);

        $params = array();
        foreach ($node->params as $param)
            $params[] = $this->pprint($param);

        $modifiers = array();
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_STATIC)
            $modifiers[] = "static()";

        $byRef = "false";
        if ($node->byRef)
            $byRef = "true";

        $fragment = "method(\"" . $node->name . "\",{" . implode(",", $modifiers) . "}," . $byRef . ",[" . implode(",", $params) . "],[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        $this->currentMethod = $priorMethod;

        return $fragment;
    }

    public function pprintConstStmt(\PhpParser\Node\Stmt\Const_ $node)
    {
        $consts = array();
        foreach ($node->consts as $const)
            $consts[] = $this->pprint($const);

        $fragment = "const([" . implode(",", $consts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintContinueStmt(\PhpParser\Node\Stmt\Continue_ $node)
    {
        if (null != $node->num)
            $fragment = "someExpr(" . $this->pprint($node->num) . ")";
        else
            $fragment = "noExpr()";

        $fragment = "\\continue(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDeclareStmt(\PhpParser\Node\Stmt\Declare_ $node)
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

    public function pprintDeclareDeclareStmt(\PhpParser\Node\Stmt\DeclareDeclare $node)
    {
        $fragment = "declaration(\"" . $node->key . "\", " . $this->pprint($node->value) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDoStmt(\PhpParser\Node\Stmt\Do_ $node)
    {
        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $fragment = "\\do(" . $this->pprint($node->cond) . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEchoStmt(\PhpParser\Node\Stmt\Echo_ $node)
    {
        $parts = array();
        foreach ($node->exprs as $expr)
            $parts[] = $this->pprint($expr);

        $fragment = "echo([" . implode(",", $parts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintElseStmt(\PhpParser\Node\Stmt\Else_ $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "\\else([" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintElseIfStmt(\PhpParser\Node\Stmt\ElseIf_ $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "elseIf(" . $this->pprint($node->cond) . ",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintExprStmt(\PhpParser\Node\Stmt\Expr $node)
    {
        $fragment = "exprstmt(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintForStmt(\PhpParser\Node\Stmt\For_ $node)
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

    public function pprintForeachStmt(\PhpParser\Node\Stmt\Foreach_ $node)
    {
        $valueVar = $this->pprint($node->valueVar);
        $expr = $this->pprint($node->expr);
        $byRef = "false";
        if ($node->byRef)
            $byRef = "true";

        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $keyvar = "noExpr()";
        if (null != $node->keyVar)
            $keyvar = "someExpr(" . $this->pprint($node->keyVar) . ")";

        $fragment = "foreach(" . $expr . "," . $keyvar . "," . $byRef . "," . $valueVar . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintFunctionStmt(\PhpParser\Node\Stmt\Function_ $node)
    {
        $priorFunction = $this->currentFunction;
        $this->currentFunction = $node->name;

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $params = array();
        foreach ($node->params as $param)
            $params[] = $this->pprint($param);

        $byRef = "false";
        if ($node->byRef)
            $byRef = "true";

        $fragment = "function(\"" . $node->name . "\"," . $byRef . ",[" . implode(",", $params) . "],[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        $this->currentFunction = $priorFunction;

        return $fragment;
    }

    public function pprintGlobalStmt(\PhpParser\Node\Stmt\Global_ $node)
    {
        $vars = array();
        foreach ($node->vars as $var)
            $vars[] = $this->pprint($var);

        $fragment = "global([" . implode(",", $vars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintGotoStmt(\PhpParser\Node\Stmt\Goto_ $node)
    {
        $fragment = "goto(\"" . $node->name . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintHaltCompilerStmt(\PhpParser\Node\Stmt\HaltCompiler $node)
    {
        $fragment = "haltCompiler(\"" . $this->rascalizeString($node->remaining) . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIfStmt(\PhpParser\Node\Stmt\If_ $node)
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

    public function pprintInlineHTMLStmt(\PhpParser\Node\Stmt\InlineHTML $node)
    {
        $fragment = "inlineHTML(\"" . $this->rascalizeString($node->value) . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintInterfaceStmt(\PhpParser\Node\Stmt\Interface_ $node)
    {
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
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLabelStmt(\PhpParser\Node\Stmt\Label $node)
    {
        $fragment = "label(\"" . $node->name . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNamespaceStmt(\PhpParser\Node\Stmt\Namespace_ $node)
    {
// If we have a non-null name, set this to the namespace name; if we
// don't, this is a global namespace declaration, like
// namespace { global stuff }
        $priorNamespace = $this->currentNamespace;
        if (null != $node->name)
            $this->currentNamespace = $node->name;
        else
            $this->currentNamespace = "";

        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

// Again check the name -- since it is optional, we return an OptionName
// here, which could be noName() if this is a global namespace
        if (null != $node->name) {
            $headerName = $this->pprint($node->name);
            $name = "someName({$headerName})";
        } else {
            $name = "noName()";
        }

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
                $fragment = "namespaceHeader({$this->pprint("")})";

        $fragment .= $this->annotateASTNode($node);

// If we had a statement body, then we reset the namespace at the end; if
// we didn't, it means that we just had a namespace declaration like
// namespace DB; which had no body, but then is still active at the end
// in which case we don't want to reset it
        if (null != $node->stmts)
            $this->currentNamespace = $priorNamespace;

        return $fragment;
    }

    public function pprintPropertyStmt(\PhpParser\Node\Stmt\Property $node)
    {
        $props = array();
        foreach ($node->props as $prop)
            $props[] = $this->pprint($prop);

        $modifiers = array();
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_STATIC)
            $modifiers[] = "static()";

        $fragment = "property({" . implode(",", $modifiers) . "},[" . implode(",", $props) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPropertyPropertyStmt(\PhpParser\Node\Stmt\PropertyProperty $node)
    {
        if (null != $node->default) {
            $fragment = "someExpr(" . $this->pprint($node->default) . ")";
        } else {
            $fragment = "noExpr()";
        }

        $fragment = "property(\"" . $node->name . "\"," . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintReturnStmt(\PhpParser\Node\Stmt\Return_ $node)
    {
        if (null != $node->expr)
            $fragment = "someExpr(" . $this->pprint($node->expr) . ")";
        else
            $fragment = "noExpr()";
        $fragment = "\\return(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticStmt(\PhpParser\Node\Stmt\Static_ $node)
    {
        $staticVars = array();
        foreach ($node->vars as $var)
            $staticVars[] = $this->pprint($var);

        $fragment = "static([" . implode(",", $staticVars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticVarStmt(\PhpParser\Node\Stmt\StaticVar $node)
    {
        $default = "noExpr()";
        if (null != $node->default)
            $default = "someExpr(" . $this->pprint($node->default) . ")";

        $fragment = "staticVar(\"" . $node->name . "\"," . $default . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintSwitchStmt(\PhpParser\Node\Stmt\Switch_ $node)
    {
        $cases = array();
        foreach ($node->cases as $case)
            $cases[] = $this->pprint($case);

        $fragment = "\\switch(" . $this->pprint($node->cond) . ",[" . implode(",", $cases) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintThrowStmt(\PhpParser\Node\Stmt\Throw_ $node)
    {
        $fragment = "\\throw(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTraitStmt(\PhpParser\Node\Stmt\Trait_ $node)
    {
        $body = array();

        $priorTrait = $this->currentTrait;
        $this->insideTrait = true;

        if (strlen($this->currentNamespace) > 0)
            $this->currentTrait = $this->currentNamespace . "\\" . $node->name;
        else
            $this->currentTrait = $node->name;

        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "trait(\"" . $node->name . "\",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        $fragment = "traitDef(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        $this->currentTrait = $priorTrait;
        $this->insideTrait = false;

        return $fragment;
    }

    public function pprintTraitUseStmt(\PhpParser\Node\Stmt\TraitUse $node)
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

    public function pprintAliasTraitUseAdaptationStmt(\PhpParser\Node\Stmt\TraitUseAdaptation\Alias $node)
    {
        if (null != $node->newName) {
            $newName = "someName(name(\"" . $node->newName . "\"))";
        } else {
            $newName = "noName()";
        }

        if (null != $node->newModifier) {
            $modifiers = array();
            if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC)
                $modifiers[] = "\\public()";
            if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED)
                $modifiers[] = "protected()";
            if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE)
                $modifiers[] = "\\private()";
            if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT)
                $modifiers[] = "abstract()";
            if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_FINAL)
                $modifiers[] = "final()";
            if ($node->type & \PhpParser\Node\Stmt\Class_::MODIFIER_STATIC)
                $modifiers[] = "static()";
            $newModifier = "{ " . implode(",", $modifiers) . " }";
        } else {
            $newModifier = "{ }";
        }

        $newMethod = "\"" . $node->method . "\"";

        if (null != $node->trait) {
            $trait = "someName(" . $this->pprint($node->trait) . ")";
        } else {
            $trait = "noName()";
        }

        $fragment = "traitAlias(" . $trait . "," . $newMethod . "," . $newModifier . "," . $newName . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPrecedenceTraitUseAdaptationStmt(\PhpParser\Node\Stmt\TraitUseAdaptation\Precedence $node)
    {
        $insteadOf = array();
        foreach ($node->insteadof as $item)
            $insteadOf[] = $this->pprint($item);

        $fragment = "traitPrecedence(" . $this->pprint($node->trait) . ",\"" . $node->method . "\",[" . implode(",", $insteadOf) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTryCatchStmt(\PhpParser\Node\Stmt\TryCatch $node)
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

        if (null != $node->finallyStmts)
            $fragment = "tryCatchFinally([" . implode(",", $body) . "],[" . implode(",", $catches) . "],[" . implode(",", $finallyBody) . "])";
        else
            $fragment = "tryCatch([" . implode(",", $body) . "],[" . implode(",", $catches) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUnsetStmt(\PhpParser\Node\Stmt\Unset_ $node)
    {
        $vars = array();
        foreach ($node->vars as $var)
            $vars[] = $this->pprint($var);

        $fragment = "unset([" . implode(",", $vars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUseStmt(\PhpParser\Node\Stmt\Use_ $node)
    {
        $uses = array();
        foreach ($node->uses as $use)
            $uses[] = $this->pprint($use);

        $fragment = "use([" . implode(",", $uses) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUseUseStmt(\PhpParser\Node\Stmt\UseUse $node)
    {
        $name = $this->pprint($node->name);
        if (null != $node->alias)
            $alias = "someName(name(\"" . $node->alias . "\"))";
        else
            $alias = "noName()";

        $fragment = "use(" . $name . "," . $alias . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintWhileStmt(\PhpParser\Node\Stmt\While_ $node)
    {
        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $fragment = "\\while(" . $this->pprint($node->cond) . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateAstNode($node);

        return $fragment;
    }
}