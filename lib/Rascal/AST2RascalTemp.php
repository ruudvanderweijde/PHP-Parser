<?php

namespace Rascal;

require __DIR__ . '/../bootstrap.php';

ini_set('xdebug.max_nesting_level', 2000);

class AST2Rascal extends BasePrinter
{

    private $filename = "";

    private $addLocations = FALSE;

    private $addLocInfo = FALSE;

    private $relativeLocations = FALSE;

    private $addIds = FALSE;

    private $addDocs = FALSE;

    private $idPrefix = "";

    private $insideTrait = FALSE;

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
    public function AST2Rascal($str, $locs, $rel, $ids, $prefix, $docs)
    {
        $this->filename = $str;
        $this->addLocations = $locs;
        $this->relativeLocations = $rel;
        $this->addIds = $ids;
        $this->idPrefix = $prefix;
        $this->addDocs = $docs;
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

    private function addLocationTag(PhpParser_Node $node)
    {
        if ($this->relativeLocations) {
            return "@at=|home://{$this->filename}|({$node->getOffset()},{$node->getLength()},<{$node->getLine()},0>,<{$node->getLine()},0>)";
        } else {
            return "@at=|file://{$this->filename}|({$node->getOffset()},{$node->getLength()},<{$node->getLine()},0>,<{$node->getLine()},0>)";
        }
    }

    private function addLocationInfo(PhpParser_Node $node)
    {
        return "@locationInfo=locationInfo("
            ."|file://{$this->filename}|({$node->getOffset()},{$node->getLength()},<{$node->getLine()},0>,<{$node->getLine()},0>),"
            ."\"{$this->currentNamespace}\"," /* current package */
            ."\"{$this->currentClass}\"," /* current class */
            ."\"{$this->currentMethod}\"," /* current method */
            ."\"{$this->currentFunction}\"" /* current function */
            .")";
    }

    /**
     * Try to extract data from PHPDoc.
     * If no PHPDoc found, return NULL
     *
     * @return string
     */
    private function addDocForNode(PhpParser_Node $node)
    {
        $docString = "@doc=\"%s\"";
        if ($doc = $node->getDocComment())
            return sprintf($docString, $this->rascalizeString($doc));
        return sprintf($docString, null);
    }

    private function annotateASTNode(PhpParser_Node $node)
    {
        $tagsToAdd = array();
        if ($this->addLocations)
            $tagsToAdd[] = $this->addLocationTag($node);
        if ($this->addLocInfo)
            $tagsToAdd[] = $this->addLocationInfo($node);
        if ($this->addIds)
            $tagsToAdd[] = $this->addUniqueId();
        if ($this->addDocs)
            $tagsToAdd[] = $this->addDocForNode($node);
        // always add location info
        $tagsToAdd[] = $this->addLocationInfo($node);

        if (count($tagsToAdd) > 0)
            return "[" . implode(",", $tagsToAdd) . "]";
        return "";
    }

    public function pprintArg(PhpParser_Node_Arg $node)
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

    public function pprintConst(PhpParser_Node_Const $node)
    {
        $fragment = "const(\"" . $node->name . "\"," . $this->pprint($node->value) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayExpr(PhpParser_Node_Expr_Array $node)
    {
        $items = array();
        foreach ($node->items as $item)
            $items[] = $this->pprint($item);

        $fragment = "array([" . implode(",", $items) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayDimFetchExpr(PhpParser_Node_Expr_ArrayDimFetch $node)
    {
        if (null != $node->dim)
            $dim = "someExpr(" . $this->pprint($node->dim) . ")";
        else
            $dim = "noExpr()";

        $fragment = "fetchArrayDim(" . $this->pprint($node->var) . "," . $dim . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayItemExpr(PhpParser_Node_Expr_ArrayItem $node)
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

    public function pprintAssignExpr(PhpParser_Node_Expr_Assign $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assign(" . $assignVar . "," . $assignExpr . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignBitwiseAndExpr(PhpParser_Node_Expr_AssignBitwiseAnd $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",bitwiseAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignBitwiseOrExpr(PhpParser_Node_Expr_AssignBitwiseOr $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",bitwiseOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignBitwiseXorExpr(PhpParser_Node_Expr_AssignBitwiseXor $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",bitwiseXor())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignConcatExpr(PhpParser_Node_Expr_AssignConcat $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",concat())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignDivExpr(PhpParser_Node_Expr_AssignDiv $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",div())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignMinusExpr(PhpParser_Node_Expr_AssignMinus $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",minus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignModExpr(PhpParser_Node_Expr_AssignMod $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",\\mod())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignMulExpr(PhpParser_Node_Expr_AssignMul $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",mul())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignPlusExpr(PhpParser_Node_Expr_AssignPlus $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",plus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignRefExpr(PhpParser_Node_Expr_AssignRef $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "refAssign(" . $assignVar . "," . $assignExpr . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignShiftLeftExpr(PhpParser_Node_Expr_AssignShiftLeft $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",leftShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintAssignShiftRightExpr(PhpParser_Node_Expr_AssignShiftRight $node)
    {
        $assignExpr = $this->pprint($node->expr);
        $assignVar = $this->pprint($node->var);

        $fragment = "assignWOp(" . $assignVar . "," . $assignExpr . ",rightShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseAndExpr(PhpParser_Node_Expr_BitwiseAnd $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",bitwiseAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseNotExpr(PhpParser_Node_Expr_BitwiseNot $node)
    {
        $expr = $this->pprint($node->expr);

        $fragment = "unaryOperation(" . $expr . ",bitwiseNot())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseOrExpr(PhpParser_Node_Expr_BitwiseOr $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",bitwiseOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBitwiseXorExpr(PhpParser_Node_Expr_BitwiseXor $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",bitwiseXor())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBooleanAndExpr(PhpParser_Node_Expr_BooleanAnd $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",booleanAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBooleanNotExpr(PhpParser_Node_Expr_BooleanNot $node)
    {
        $expr = $this->pprint($node->expr);

        $fragment = "unaryOperation(" . $expr . ",booleanNot())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBooleanOrExpr(PhpParser_Node_Expr_BooleanOr $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",booleanOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintArrayCastExpr(PhpParser_Node_Expr_Cast_Array $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(array()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintBoolCastExpr(PhpParser_Node_Expr_Cast_Bool $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(\\bool()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintDoubleCastExpr(PhpParser_Node_Expr_Cast_Double $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(float()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintIntCastExpr(PhpParser_Node_Expr_Cast_Int $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(\\int()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintObjectCastExpr(PhpParser_Node_Expr_Cast_Object $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(object()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintStringCastExpr(PhpParser_Node_Expr_Cast_String $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(string()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintUnsetCastExpr(PhpParser_Node_Expr_Cast_Unset $node)
    {
        $toCast = $this->pprint($node->expr);
        $fragment = "cast(unset()," . $toCast . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintClassConstFetchExpr(PhpParser_Node_Expr_ClassConstFetch $node)
    {
        $name = $this->pprint($node->class);
        if ($node->class instanceof PhpParser_Node_Name)
            $name = "name({$name})";
        else
            $name = "expr({$name})";

        $fragment = "fetchClassConst(" . $name . ",\"" . $node->name . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintCloneExpr(PhpParser_Node_Expr_Clone $node)
    {
        $fragment = "clone(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintClosureExpr(PhpParser_Node_Expr_Closure $node)
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

    public function pprintClosureUseExpr(PhpParser_Node_Expr_ClosureUse $node)
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

    public function pprintConcatExpr(PhpParser_Node_Expr_Concat $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",concat())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintConstFetchExpr(PhpParser_Node_Expr_ConstFetch $node)
    {
        $fragment = "fetchConst(" . $this->pprint($node->name) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintDivExpr(PhpParser_Node_Expr_Div $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",div())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEmptyExpr(PhpParser_Node_Expr_Empty $node)
    {
        $fragment = "empty(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintEqualExpr(PhpParser_Node_Expr_Equal $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",equal())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintErrorSuppressExpr(PhpParser_Node_Expr_ErrorSuppress $node)
    {
        $fragment = "suppress(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintEvalExpr(PhpParser_Node_Expr_Eval $node)
    {
        $fragment = "eval(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintExitExpr(PhpParser_Node_Expr_Exit $node)
    {
        if (null != $node->expr)
            $fragment = "someExpr(" . $this->pprint($node->expr) . ")";
        else
            $fragment = "noExpr()";
        $fragment = "exit(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintFuncCallExpr(PhpParser_Node_Expr_FuncCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->pprint($node->name);
        if ($node->name instanceof PhpParser_Node_Name)
            $name = "name({$name})";
        else
            $name = "expr({$name})";

        $fragment = "call(" . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintGreaterExpr(PhpParser_Node_Expr_Greater $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",gt())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintGreaterOrEqualExpr(PhpParser_Node_Expr_GreaterOrEqual $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",geq())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIdenticalExpr(PhpParser_Node_Expr_Identical $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",identical())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIncludeExpr(PhpParser_Node_Expr_Include $node)
    {
        $fragment = "include(" . $this->pprint($node->expr) . ",";
        if (PhpParser_Node_Expr_Include::TYPE_INCLUDE == $node->type)
            $fragment .= "include()";
        elseif (PhpParser_Node_Expr_Include::TYPE_INCLUDE_ONCE == $node->type)
            $fragment .= "includeOnce()";
        elseif (PhpParser_Node_Expr_Include::TYPE_REQUIRE == $node->type)
            $fragment .= "require()";
        else
            $fragment .= "requireOnce()";
        $fragment .= ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintInstanceofExpr(PhpParser_Node_Expr_Instanceof $node)
    {
        $right = $this->pprint($node->class);
        if ($node->class instanceof PhpParser_Node_Name)
            $right = "name({$right})";
        else
            $right = "expr({$right})";

        $left = $this->pprint($node->expr);

        $fragment = "instanceOf(" . $left . "," . $right . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIssetExpr(PhpParser_Node_Expr_Isset $node)
    {
        $exprs = array();
        foreach ($node->vars as $var)
            $exprs[] = $this->pprint($var);

        $fragment = "isSet([" . implode(",", $exprs) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintListExpr(PhpParser_Node_Expr_List $node)
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

    public function pprintLogicalAndExpr(PhpParser_Node_Expr_LogicalAnd $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",logicalAnd())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLogicalOrExpr(PhpParser_Node_Expr_LogicalOr $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",logicalOr())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLogicalXorExpr(PhpParser_Node_Expr_LogicalXor $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",logicalXor())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMethodCallExpr(PhpParser_Node_Expr_MethodCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        if ($node->name instanceof PhpParser_Node_Expr) {
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

    public function pprintMinusExpr(PhpParser_Node_Expr_Minus $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",minus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintModExpr(PhpParser_Node_Expr_Mod $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",\\mod())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMulExpr(PhpParser_Node_Expr_Mul $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",mul())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNewExpr(PhpParser_Node_Expr_New $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        $name = $this->pprint($node->class);

        if ($node->class instanceof PhpParser_Node_Expr)
            $name = "expr({$name})";
        else
            $name = "name({$name})";

        $fragment = "new(" . $name . ",[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNotEqualExpr(PhpParser_Node_Expr_NotEqual $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",notEqual())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNotIdenticalExpr(PhpParser_Node_Expr_NotIdentical $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",notIdentical())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPlusExpr(PhpParser_Node_Expr_Plus $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",plus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPostDecExpr(PhpParser_Node_Expr_PostDec $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",postDec())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPostIncExpr(PhpParser_Node_Expr_PostInc $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",postInc())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPreDecExpr(PhpParser_Node_Expr_PreDec $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",preDec())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPreIncExpr(PhpParser_Node_Expr_PreInc $node)
    {
        $operand = $this->pprint($node->var);
        $fragment = "unaryOperation(" . $operand . ",preInc())";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPrintExpr(PhpParser_Node_Expr_Print $node)
    {
        $operand = $this->pprint($node->expr);
        $fragment = "print(" . $operand . ")";
        $fragment .= $this->annotateASTNode($node);
        return $fragment;
    }

    public function pprintPropertyFetchExpr(PhpParser_Node_Expr_PropertyFetch $node)
    {
        if ($node->name instanceof PhpParser_Node_Expr) {
            $fragment = "expr(" . $this->pprint($node->name) . ")";
        } else {
            $fragment = "name(name(\"" . $node->name . "\"))";
        }

        $fragment = "propertyFetch(" . $this->pprint($node->var) . "," . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShellExecExpr(PhpParser_Node_Expr_ShellExec $node)
    {
        $parts = array();
        foreach ($node->parts as $item) {
            if ($item instanceof PhpParser_Node_Expr) {
                $parts[] = $this->pprint($item);
            } else {
                $parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
            }
        }

        $fragment = "shellExec([" . implode(",", $parts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShiftLeftExpr(PhpParser_Node_Expr_ShiftLeft $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",leftShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintShiftRightExpr(PhpParser_Node_Expr_ShiftRight $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",rightShift())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintSmallerExpr(PhpParser_Node_Expr_Smaller $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",lt())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintSmallerOrEqualExpr(PhpParser_Node_Expr_SmallerOrEqual $node)
    {
        $right = $this->pprint($node->right);
        $left = $this->pprint($node->left);

        $fragment = "binaryOperation(" . $left . "," . $right . ",leq())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticCallExpr(PhpParser_Node_Expr_StaticCall $node)
    {
        $args = array();
        foreach ($node->args as $arg)
            $args[] = $this->pprint($arg);

        if ($node->name instanceof PhpParser_Node_Expr)
            $name = "expr(" . $this->pprint($node->name) . ")";
        else
            $name = "name(name(\"" . $node->name . "\"))";

        if ($node->class instanceof PhpParser_Node_Expr) {
            $class = "expr(" . $this->pprint($node->class) . ")";
        } else {
            $class = "name(" . $this->pprint($node->class) . ")";
        }

        $fragment = "staticCall({$class},{$name},[" . implode(",", $args) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticPropertyFetchExpr(PhpParser_Node_Expr_StaticPropertyFetch $node)
    {
        if ($node->name instanceof PhpParser_Node_Expr) {
            $name = "expr(" . $this->pprint($node->name) . ")";
        } else {
            $name = "name(name(\"" . $node->name . "\"))";
        }

        if ($node->class instanceof PhpParser_Node_Expr) {
            $class = "expr(" . $this->pprint($node->class) . ")";
        } else {
            $class = "name(" . $this->pprint($node->class) . ")";
        }

        $fragment = "staticPropertyFetch({$class},{$name})";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTernaryExpr(PhpParser_Node_Expr_Ternary $node)
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

    public function pprintUnaryMinusExpr(PhpParser_Node_Expr_UnaryMinus $node)
    {
        $operand = $this->pprint($node->expr);
        $fragment = "unaryOperation(" . $operand . ",unaryMinus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUnaryPlusExpr(PhpParser_Node_Expr_UnaryPlus $node)
    {
        $operand = $this->pprint($node->expr);
        $fragment = "unaryOperation(" . $operand . ",unaryPlus())";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintVariableExpr(PhpParser_Node_Expr_Variable $node)
    {
        if ($node->name instanceof PhpParser_Node_Expr) {
            $fragment = "expr(" . $this->pprint($node->name) . ")";
        } else {
            $fragment = "name(name(\"" . $node->name . "\"))";
        }
        $fragment = "var(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintYieldExpr(PhpParser_Node_Expr_Yield $node)
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

    public function pprintFullyQualifiedName(PhpParser_Node_Name_FullyQualified $node)
    {
        return $this->pprintName($node);
    }

    public function pprintRelativeName(PhpParser_Node_Name_Relative $node)
    {
        return $this->pprintName($node);
    }

    public function pprintName(PhpParser_Node_Name $node)
    {
        if (is_array($node->parts))
            $fragment = implode("::", $node->parts);
        else
            $fragment = $node->parts;
        $fragment = "name(\"" . $fragment . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintParam(PhpParser_Node_Param $node)
    {
        if (null == $node->type) {
            $type = "noName()";
        } else {
            if ($node->type instanceof PhpParser_Node_Name) {
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

    public function pprintClassConstScalar(PhpParser_Node_Scalar_ClassConst $node)
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

    public function pprintDirConstScalar(PhpParser_Node_Scalar_DirConst $node)
    {
        $fragment = "dirConstant()[@actualValue=\"{dirname($this->filename)}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDNumberScalar(PhpParser_Node_Scalar_DNumber $node)
    {
        $fragment = "float(" . sprintf('%f', $node->value) . ")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEncapsedScalar(PhpParser_Node_Scalar_Encapsed $node)
    {
        $parts = array();
        foreach ($node->parts as $item) {
            if ($item instanceof PhpParser_Node_Expr) {
                $parts[] = $this->pprint($item);
            } else {
                $parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
            }
        }
        $fragment = "scalar(encapsed([" . implode(",", $parts) . "]))";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintFileConstScalar(PhpParser_Node_Scalar_FileConst $node)
    {
        $fragment = "fileConstant()[@actualValue=\"{$this->filename}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintFuncConstScalar(PhpParser_Node_Scalar_FuncConst $node)
    {
        $fragment = "funcConstant()[@actualValue=\"{$this->currentFunction}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLineConstScalar(PhpParser_Node_Scalar_LineConst $node)
    {
        $fragment = "lineConstant()[@actualValue=\"{$node->getLine()}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintLNumberScalar(PhpParser_Node_Scalar_LNumber $node)
    {
        $fragment = "integer(" . sprintf('%d', $node->value) . ")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintMethodConstScalar(PhpParser_Node_Scalar_MethodConst $node)
    {
        $fragment = "methodConstant()[@actualValue=\"{$this->currentMethod}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNSConstScalar(PhpParser_Node_Scalar_NSConst $node)
    {
        $fragment = "namespaceConstant()[@actualValue=\"{$this->currentNamespace}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStringScalar(PhpParser_Node_Scalar_String $node)
    {
        $fragment = "string(\"" . $this->rascalizeString($node->value) . "\")";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTraitConstScalar(PhpParser_Node_Scalar_TraitConst $node)
    {
        $fragment = "traitConstant()[@actualValue=\"{$this->currentTrait}\"]";
        $fragment = "scalar(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintBreakStmt(PhpParser_Node_Stmt_Break $node)
    {
        if (null != $node->num)
            $fragment = "someExpr(" . $this->pprint($node->num) . ")";
        else
            $fragment = "noExpr()";

        $fragment = "\\break(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintCaseStmt(PhpParser_Node_Stmt_Case $node)
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

    public function pprintCatchStmt(PhpParser_Node_Stmt_Catch $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $xtype = $this->pprint($node->type);

        $fragment = "\\catch(" . $xtype . ",\"" . $node->var . "\",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassStmt(PhpParser_Node_Stmt_Class $node)
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
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_STATIC)
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

    public function pprintClassConstStmt(PhpParser_Node_Stmt_ClassConst $node)
    {
        $consts = array();
        foreach ($node->consts as $const)
            $consts[] = $this->pprint($const);

        $fragment = "constCI([" . implode(",", $consts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintClassMethodStmt(PhpParser_Node_Stmt_ClassMethod $node)
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
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_STATIC)
            $modifiers[] = "static()";

        $byRef = "false";
        if ($node->byRef)
            $byRef = "true";

        $fragment = "method(\"" . $node->name . "\",{" . implode(",", $modifiers) . "}," . $byRef . ",[" . implode(",", $params) . "],[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        $this->currentMethod = $priorMethod;

        return $fragment;
    }

    public function pprintConstStmt(PhpParser_Node_Stmt_Const $node)
    {
        $consts = array();
        foreach ($node->consts as $const)
            $consts[] = $this->pprint($const);

        $fragment = "const([" . implode(",", $consts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintContinueStmt(PhpParser_Node_Stmt_Continue $node)
    {
        if (null != $node->num)
            $fragment = "someExpr(" . $this->pprint($node->num) . ")";
        else
            $fragment = "noExpr()";

        $fragment = "\\continue(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDeclareStmt(PhpParser_Node_Stmt_Declare $node)
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

    public function pprintDeclareDeclareStmt(PhpParser_Node_Stmt_DeclareDeclare $node)
    {
        $fragment = "declaration(\"" . $node->key . "\", " . $this->pprint($node->value) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintDoStmt(PhpParser_Node_Stmt_Do $node)
    {
        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $fragment = "\\do(" . $this->pprint($node->cond) . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintEchoStmt(PhpParser_Node_Stmt_Echo $node)
    {
        $parts = array();
        foreach ($node->exprs as $expr)
            $parts[] = $this->pprint($expr);

        $fragment = "echo([" . implode(",", $parts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintElseStmt(PhpParser_Node_Stmt_Else $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "\\else([" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintElseIfStmt(PhpParser_Node_Stmt_ElseIf $node)
    {
        $body = array();
        foreach ($node->stmts as $stmt)
            $body[] = $this->pprint($stmt);

        $fragment = "elseIf(" . $this->pprint($node->cond) . ",[" . implode(",", $body) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintExprStmt(PhpParser_Node_Stmt_Expr $node)
    {
        $fragment = "exprstmt(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintForStmt(PhpParser_Node_Stmt_For $node)
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

    public function pprintForeachStmt(PhpParser_Node_Stmt_Foreach $node)
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

    public function pprintFunctionStmt(PhpParser_Node_Stmt_Function $node)
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

    public function pprintGlobalStmt(PhpParser_Node_Stmt_Global $node)
    {
        $vars = array();
        foreach ($node->vars as $var)
            $vars[] = $this->pprint($var);

        $fragment = "global([" . implode(",", $vars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintGotoStmt(PhpParser_Node_Stmt_Goto $node)
    {
        $fragment = "goto(\"" . $node->name . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintHaltCompilerStmt(PhpParser_Node_Stmt_HaltCompiler $node)
    {
        $fragment = "haltCompiler(\"" . $this->rascalizeString($node->remaining) . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintIfStmt(PhpParser_Node_Stmt_If $node)
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

    public function pprintInlineHTMLStmt(PhpParser_Node_Stmt_InlineHTML $node)
    {
        $fragment = "inlineHTML(\"" . $this->rascalizeString($node->value) . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintInterfaceStmt(PhpParser_Node_Stmt_Interface $node)
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

    public function pprintLabelStmt(PhpParser_Node_Stmt_Label $node)
    {
        $fragment = "label(\"" . $node->name . "\")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintNamespaceStmt(PhpParser_Node_Stmt_Namespace $node)
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

    public function pprintPropertyStmt(PhpParser_Node_Stmt_Property $node)
    {
        $props = array();
        foreach ($node->props as $prop)
            $props[] = $this->pprint($prop);

        $modifiers = array();
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PUBLIC)
            $modifiers[] = "\\public()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PROTECTED)
            $modifiers[] = "protected()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PRIVATE)
            $modifiers[] = "\\private()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_ABSTRACT)
            $modifiers[] = "abstract()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_FINAL)
            $modifiers[] = "final()";
        if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_STATIC)
            $modifiers[] = "static()";

        $fragment = "property({" . implode(",", $modifiers) . "},[" . implode(",", $props) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintPropertyPropertyStmt(PhpParser_Node_Stmt_PropertyProperty $node)
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

    public function pprintReturnStmt(PhpParser_Node_Stmt_Return $node)
    {
        if (null != $node->expr)
            $fragment = "someExpr(" . $this->pprint($node->expr) . ")";
        else
            $fragment = "noExpr()";
        $fragment = "\\return(" . $fragment . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticStmt(PhpParser_Node_Stmt_Static $node)
    {
        $staticVars = array();
        foreach ($node->vars as $var)
            $staticVars[] = $this->pprint($var);

        $fragment = "static([" . implode(",", $staticVars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintStaticVarStmt(PhpParser_Node_Stmt_StaticVar $node)
    {
        $default = "noExpr()";
        if (null != $node->default)
            $default = "someExpr(" . $this->pprint($node->default) . ")";

        $fragment = "staticVar(\"" . $node->name . "\"," . $default . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintSwitchStmt(PhpParser_Node_Stmt_Switch $node)
    {
        $cases = array();
        foreach ($node->cases as $case)
            $cases[] = $this->pprint($case);

        $fragment = "\\switch(" . $this->pprint($node->cond) . ",[" . implode(",", $cases) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintThrowStmt(PhpParser_Node_Stmt_Throw $node)
    {
        $fragment = "\\throw(" . $this->pprint($node->expr) . ")";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTraitStmt(PhpParser_Node_Stmt_Trait $node)
    {
        $body = array();

        $priorTrait = $this->currentTrait;
        $this->insideTrait = TRUE;

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
        $this->insideTrait = FALSE;

        return $fragment;
    }

    public function pprintTraitUseStmt(PhpParser_Node_Stmt_TraitUse $node)
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

    public function pprintAliasTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation_Alias $node)
    {
        if (null != $node->newName) {
            $newName = "someName(name(\"" . $node->newName . "\"))";
        } else {
            $newName = "noName()";
        }

        if (null != $node->newModifier) {
            $modifiers = array();
            if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PUBLIC)
                $modifiers[] = "\\public()";
            if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PROTECTED)
                $modifiers[] = "protected()";
            if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_PRIVATE)
                $modifiers[] = "\\private()";
            if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_ABSTRACT)
                $modifiers[] = "abstract()";
            if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_FINAL)
                $modifiers[] = "final()";
            if ($node->type & PhpParser_Node_Stmt_Class::MODIFIER_STATIC)
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

    public function pprintPrecedenceTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation_Precedence $node)
    {
        $insteadOf = array();
        foreach ($node->insteadof as $item)
            $insteadOf[] = $this->pprint($item);

        $fragment = "traitPrecedence(" . $this->pprint($node->trait) . ",\"" . $node->method . "\",[" . implode(",", $insteadOf) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintTryCatchStmt(PhpParser_Node_Stmt_TryCatch $node)
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

    public function pprintUnsetStmt(PhpParser_Node_Stmt_Unset $node)
    {
        $vars = array();
        foreach ($node->vars as $var)
            $vars[] = $this->pprint($var);

        $fragment = "unset([" . implode(",", $vars) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUseStmt(PhpParser_Node_Stmt_Use $node)
    {
        $uses = array();
        foreach ($node->uses as $use)
            $uses[] = $this->pprint($use);

        $fragment = "use([" . implode(",", $uses) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }

    public function pprintUseUseStmt(PhpParser_Node_Stmt_UseUse $node)
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

    public function pprintWhileStmt(PhpParser_Node_Stmt_While $node)
    {
        $stmts = array();
        foreach ($node->stmts as $stmt)
            $stmts[] = $this->pprint($stmt);

        $fragment = "\\while(" . $this->pprint($node->cond) . ",[" . implode(",", $stmts) . "])";
        $fragment .= $this->annotateASTNode($node);

        return $fragment;
    }
}

if (count($argv) < 2) {
    echo "Expected at least 1 argument\n";
    exit() - 1;
}

$opts = getopt("f:lirp:", array(
    "file:",
    "enableLocations",
    "uniqueIds",
    "relativeLocations",
    "prefix:"
));

if (isset($opts["f"]))
    $file = $opts["f"];
else 
    if (isset($opts["file"]))
        $file = $opts["file"];
    else 
        if (count($argv) == 2) {
            $file = $argv[1];
        } else {
            echo "errscript(\"The file must be provided using either -f or --file\")";
            exit() - 1;
        }

$enableLocations = FALSE;
if (isset($opts["l"]) || isset($opts["enableLocations"]))
    $enableLocations = TRUE;

$uniqueIds = FALSE;
if (isset($opts["i"]) || isset($opts["uniqueIds"]))
    $uniqueIds = TRUE;

if (isset($opts["p"]))
    $prefix = $opts["p"] . '.';
else 
    if (isset($opts["prefix"]))
        $prefix = $opts["prefix"] . '.';
    else {
        $prefix = "";
    }

$relativeLocations = FALSE;
if (isset($opts["r"]) || isset($opts["relativeLocations"]))
    $relativeLocations = TRUE;

if (isset($_SERVER['HOME'])) {
    $homedir = $_SERVER['HOME'];
} else {
    $homedir = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
}

$inputCode = '';
if (! $relativeLocations && file_exists($file))
    $inputCode = file_get_contents($file);
else 
    if ($relativeLocations && file_exists($homedir . $file))
        $inputCode = file_get_contents($homedir . $file);
    else {
        echo "errscript(\"The given file, $file, does not exist\")";
        exit() - 1;
    }

$addPHPDocs = TRUE;

$parser = new \PhpParser\Parser(new \PhpParser\Lexer());
$dumper = new \PhpParser\NodeDumper();
$printer = new AST2Rascal($file, $enableLocations, $relativeLocations, $uniqueIds, $prefix, $addPHPDocs);
$traverser = new \PhpParser\NodeTraverser;
$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver);

try {
    $stmts = $parser->parse($inputCode);
    writeOutputToTmpFolder($file, "------[ AFTER PARSING ]-------\n" . print_r($stmts,1));
    $stmts = $traverser->traverse($stmts);
    // cache the output of the parsed files in the case
    writeOutputToTmpFolder($file, "\n\n------[ AFTER NAME RESOLVE ]-------\n" . print_r($stmts,1), FILE_APPEND);
    $strStmts = array();
    foreach ($stmts as $stmt)
        $strStmts[] = $printer->pprint($stmt);
    $script = implode(",\n", $strStmts);
    echo "script([" . $script . "])";

    // write prettyprint output
    writeOutputToTmpFolder($file, "\n-------[ RASCAL OUTPUT ]-------\nscript([" . $script . "])\n\nGenerated at: ".date('Y-m-d H:i:s'), FILE_APPEND);

} catch (PhpParser_Error $e) {
    echo "errscript(\"" . $printer->rascalizeString($e->getMessage()) . "\")";
} catch (Exception $e) {
    echo "errscript(\"" . $printer->rascalizeString($e->getMessage()) . "\")";
}

function writeOutputToTmpFolder($file, $data, $flags = 0) {
    $tempFileName = preg_replace('#(.*)/([\w\-_\.]+)/([\w-_\.]+\.[\w]{2,4})$#i', '/tmp/\2_\3', $file);
    file_put_contents($tempFileName, $data, $flags);
}
?>