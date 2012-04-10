<?php
require '../PHPParser/Autoloader.php';
PHPParser_Autoloader::register();

require_once 'IPrinter.php';
require_once 'BasePrinter.php';

class AST2Rascal extends BasePrinter {
  private $filename = "";

  public function AST2Rascal($str)
  {
    $this->filename = $str;
  }

  private function rascalizeString($str) 
  {
    $newstr = "";
    foreach(str_split($str) as $char) {
      if ("<" == $char)
	$newstr .= "\\<";
      elseif (">" == $char)
	$newstr .= "\\>";
      elseif ("'" == $char)
	$newstr .= "\\'";
      elseif ("\n" == $char)
	$newstr .= "\\n";
      elseif ("\\" == $char)
	$newstr .= "\\\\";
      elseif ("\"" == $char)
	$newstr .= "\\\"";
      else
	$newstr .= $char;
    }
    return $newstr;
  }

  private function rascalizeStringLiteral($str) 
  {
    $newstr = "";
    foreach(str_split($str) as $char) {
      if ("<" == $char)
	$newstr .= "\\<";
      elseif (">" == $char)
	$newstr .= "\\>";
      elseif ("'" == $char)
	$newstr .= "\\'";
      elseif ("\n" == $char)
	$newstr .= "\\n";
      elseif ("\\" == $char)
	$newstr .= "\\\\";
      elseif ("\"" == $char)
	$newstr .= "\\\"";
      else
	$newstr .= $char;
    }
    return $newstr;
  }

  private function tagWithLine(PHPParser_Node $node)
  {
    return "[@at=|file://{$this->filename}|(0,0,<{$node->getLine()},0>,<{$node->getLine()},0>)]";
    //return "";
  }

  public function pprintArg(PHPParser_Node_Arg $node)
  {
    $argValue = $this->pprint($node->value);

    if ($node->byRef)
      $byRef = "true";
    else
      $byRef = "false";
    
    $fragment = "actualParameter(" . $argValue . "," . $byRef . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintConst(PHPParser_Node_Const $node)
  {
    $fragment = "const(\"" . $this->rascalizeString($node->name) . "\"," . $this->pprint($node->value) . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintArrayExpr(PHPParser_Node_Expr_Array $node)
  {
    $items = array();
    foreach($node->items as $item)
      $items[] = $this->pprint($item);

    $fragment = "array([" . implode(",",$items) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintArrayDimFetchExpr(PHPParser_Node_Expr_ArrayDimFetch $node)
  {
    if (null != $node->dim)
      $dim = "someExpr(" . $this->pprint($node->dim) . ")";
    else
      $dim = "noExpr()";

    $fragment = "fetchArrayDim(" . $this->pprint($node->var) . "," . $dim . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintArrayItemExpr(PHPParser_Node_Expr_ArrayItem $node)
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
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintAssignExpr(PHPParser_Node_Expr_Assign $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assign(".$assignVar.",".$assignExpr.")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintAssignBitwiseAndExpr(PHPParser_Node_Expr_AssignBitwiseAnd $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",bitwiseAnd())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignBitwiseOrExpr(PHPParser_Node_Expr_AssignBitwiseOr $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",bitwiseOr())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignBitwiseXorExpr(PHPParser_Node_Expr_AssignBitwiseXor $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",bitwiseXor())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignConcatExpr(PHPParser_Node_Expr_AssignConcat $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",concat())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignDivExpr(PHPParser_Node_Expr_AssignDiv $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",div())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignListExpr(PHPParser_Node_Expr_AssignList $node)
  {
    $assignExpr = $this->pprint($node->expr);

    $assignVars = array();
    foreach($node->vars as $var) {
      if (null != $var) {
	$assignVars[] = "someExpr(" . $this->pprint($var) . ")";
      } else {
	$assignVars[] = "noExpr()";
      }
    }

    $fragment = "listAssign([" . implode(",",$assignVars) . "]," . $assignExpr . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignMinusExpr(PHPParser_Node_Expr_AssignMinus $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",minus())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignModExpr(PHPParser_Node_Expr_AssignMod $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",\\mod())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignMulExpr(PHPParser_Node_Expr_AssignMul $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",mul())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignPlusExpr(PHPParser_Node_Expr_AssignPlus $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",plus())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignRefExpr(PHPParser_Node_Expr_AssignRef $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "refAssign(".$assignVar.",".$assignExpr.")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignShiftLeftExpr(PHPParser_Node_Expr_AssignShiftLeft $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",leftShift())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAssignShiftRightExpr(PHPParser_Node_Expr_AssignShiftRight $node)
  {
    $assignExpr = $this->pprint($node->expr);
    $assignVar = $this->pprint($node->var);

    $fragment = "assignWOp(".$assignVar.",".$assignExpr.",rightShift())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintBitwiseAndExpr(PHPParser_Node_Expr_BitwiseAnd $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",bitwiseAnd())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintBitwiseNotExpr(PHPParser_Node_Expr_BitwiseNot $node)
  {
    $expr = $this->pprint($node->expr);

    $fragment = "unaryOperation(".$expr.",bitwiseNot())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
  
  public function pprintBitwiseOrExpr(PHPParser_Node_Expr_BitwiseOr $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",bitwiseOr())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintBitwiseXorExpr(PHPParser_Node_Expr_BitwiseXor $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",bitwiseXor())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintBooleanAndExpr(PHPParser_Node_Expr_BooleanAnd $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",booleanAnd())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintBooleanNotExpr(PHPParser_Node_Expr_BooleanNot $node)
  {
    $expr = $this->pprint($node->expr);

    $fragment = "unaryOperation(".$expr.",booleanNot())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintBooleanOrExpr(PHPParser_Node_Expr_BooleanOr $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",booleanOr())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintArrayCastExpr(PHPParser_Node_Expr_Cast_Array $node)
  {
    $toCast = $this->pprint($node->expr);
    $fragment = "cast(array()," . $toCast . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintBoolCastExpr(PHPParser_Node_Expr_Cast_Bool $node)
  {
    $toCast = $this->pprint($node->expr);
    $fragment = "cast(\bool()," . $toCast . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintDoubleCastExpr(PHPParser_Node_Expr_Cast_Double $node)
  {
    $toCast = $this->pprint($node->expr);
    $fragment = "cast(float()," . $toCast . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintIntCastExpr(PHPParser_Node_Expr_Cast_Int $node)
  {
    $toCast = $this->pprint($node->expr);
    $fragment = "cast(\int()," . $toCast . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintObjectCastExpr(PHPParser_Node_Expr_Cast_Object $node)
  {
    $toCast = $this->pprint($node->expr);
    $fragment = "cast(object()," . $toCast . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintStringCastExpr(PHPParser_Node_Expr_Cast_String $node)
  {
    $toCast = $this->pprint($node->expr);
    $fragment = "cast(string()," . $toCast . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintUnsetCastExpr(PHPParser_Node_Expr_Cast_Unset $node)
  {
    $toCast = $this->pprint($node->expr);
    $fragment = "cast(unset()," . $toCast . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }

  public function pprintClassConstFetchExpr(PHPParser_Node_Expr_ClassConstFetch $node)
  {
    $name = $this->pprint($node->class);
    if ($node->class instanceof PHPParser_Node_Name)
      $name = "name({$name})";
    else
      $name = "expr({$name})";

    $fragment = "fetchClassConst(" . $name . ",\"" . $this->rascalizeString($node->name) . "\")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintCloneExpr(PHPParser_Node_Expr_Clone $node)
  {
    $fragment = "clone(" . $this->pprint($node->expr) . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintClosureExpr(PHPParser_Node_Expr_Closure $node)
  {
    $body = array();
    $params = array();
    $uses = array();

    foreach($node->uses as $use)
      $uses[] = $this->pprint($use);
    foreach($node->params as $param)
      $params[] = $this->pprint($param);
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);
    
    $fragment = "closure([" . implode(",",$body) . "],[";
    $fragment .= implode(",",$params) . "],[";
    $fragment .= implode(",",$uses) . "],";
    if ($node->byRef)
      $fragment .= "true,";
    else
      $fragment .= "false,";
    if ($node->static)
      $fragment .= "true";
    else
      $fragment .= "false";
    $fragment .= ")";
    
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintClosureUseExpr(PHPParser_Node_Expr_ClosureUse $node)
  {
    $fragment = "closureUse(\"" . $node->var . "\",";
    if ($node->byRef)
      $fragment .= "true";
    else
      $fragment .= "false";
    $fragment .= ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintConcatExpr(PHPParser_Node_Expr_Concat $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",concat())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintConstFetchExpr(PHPParser_Node_Expr_ConstFetch $node)
  {
    $fragment = "fetchConst(" . $this->pprint($node->name) . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }

  public function pprintDivExpr(PHPParser_Node_Expr_Div $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",div())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintEmptyExpr(PHPParser_Node_Expr_Empty $node)
  {
    $fragment = "empty(" . $this->pprint($node->var) . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
  
  public function pprintEqualExpr(PHPParser_Node_Expr_Equal $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",equal())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintErrorSuppressExpr(PHPParser_Node_Expr_ErrorSuppress $node)
  {
    $fragment = "suppress(" . $this->pprint($node->expr) . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintEvalExpr(PHPParser_Node_Expr_Eval $node)
  {
    $fragment = "eval(" . $this->pprint($node->expr) . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintExitExpr(PHPParser_Node_Expr_Exit $node)
  {
    if (null != $node->expr)
      $fragment = "someExpr(" . $this->pprint($node->expr) . ")";
    else
      $fragment = "noExpr()";
    $fragment = "exit(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintFuncCallExpr(PHPParser_Node_Expr_FuncCall $node)
  {
    $args = array();
    foreach($node->args as $arg)
      $args[] = $this->pprint($arg);

    $name = $this->pprint($node->name);
    if ($node->name instanceof PHPParser_Node_Name)
      $name = "name({$name})";
    else
      $name = "expr({$name})";

    $fragment = "call(" . $name . ",[" . implode(",",$args) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintGreaterExpr(PHPParser_Node_Expr_Greater $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",gt())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintGreaterOrEqualExpr(PHPParser_Node_Expr_GreaterOrEqual $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",geq())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintIdenticalExpr(PHPParser_Node_Expr_Identical $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",identical())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintIncludeExpr(PHPParser_Node_Expr_Include $node)
  {
    $fragment = "include(" . $this->pprint($node->expr) . ",";
    if (PHPParser_Node_Expr_Include::TYPE_INCLUDE == $node->type)
      $fragment .= "include()";
    elseif (PHPParser_Node_Expr_Include::TYPE_INCLUDE_ONCE == $node->type)
      $fragment .= "includeOnce()";
    elseif (PHPParser_Node_Expr_Include::TYPE_REQUIRE == $node->type)
      $fragment .= "require()";
    else
      $fragment .= "requireOnce()";
    $fragment .= ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintInstanceofExpr(PHPParser_Node_Expr_Instanceof $node)
  {
    $right = $this->pprint($node->class);
    if ($node->class instanceOf PHPParser_Node_Name)
      $right = "name({$right})";
    else
      $right = "expr({$right})";

    $left = $this->pprint($node->expr);

    $fragment = "instanceOf(".$left.",".$right.")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintIssetExpr(PHPParser_Node_Expr_Isset $node)
  {
    $exprs = array();
    foreach($node->vars as $var)
      $exprs[] = $this->pprint($var);

    $fragment = "isSet([" . implode(",",$exprs) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintLogicalAndExpr(PHPParser_Node_Expr_LogicalAnd $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",logicalAnd())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintLogicalOrExpr(PHPParser_Node_Expr_LogicalOr $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",logicalOr())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
  	
  public function pprintLogicalXorExpr(PHPParser_Node_Expr_LogicalXor $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",logicalXor())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintMethodCallExpr(PHPParser_Node_Expr_MethodCall $node)
  {
    $args = array();
    foreach($node->args as $arg)
      $args[] = $this->pprint($arg);

    if ($node->name instanceof PHPParser_Node_Expr) {
      $name = $this->pprint($node->name);
      $name = "expr({$name})";
    } else {
      $name = "name(name(\"" . $node->name . "\"))";
    }

    $target = $this->pprint($node->var);

    $fragment = "methodCall(" . $target . "," . $name . ",[" . implode(",",$args) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintMinusExpr(PHPParser_Node_Expr_Minus $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",minus())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintModExpr(PHPParser_Node_Expr_Mod $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",\\mod())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintMulExpr(PHPParser_Node_Expr_Mul $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",mul())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintNewExpr(PHPParser_Node_Expr_New $node)
  {
    $args = array();
    foreach ($node->args as $arg)
      $args[] = $this->pprint($arg);

    $name = $this->pprint($node->class);

    if ($node->class instanceof PHPParser_Node_Expr)
      $name = "expr({$name})";
    else
      $name = "name({$name})";

    $fragment = "new(" . $name . ",[" . implode(",",$args) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintNotEqualExpr(PHPParser_Node_Expr_NotEqual $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",notEqual())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintNotIdenticalExpr(PHPParser_Node_Expr_NotIdentical $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",notIdentical())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintPlusExpr(PHPParser_Node_Expr_Plus $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",plus())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintPostDecExpr(PHPParser_Node_Expr_PostDec $node)
  {
    $operand = $this->pprint($node->var);
    $fragment = "unaryOperation(".$operand.",postDec())";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }

  public function pprintPostIncExpr(PHPParser_Node_Expr_PostInc $node)
  {
    $operand = $this->pprint($node->var);
    $fragment = "unaryOperation(".$operand.",postInc())";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }

  public function pprintPreDecExpr(PHPParser_Node_Expr_PreDec $node)
  {
    $operand = $this->pprint($node->var);
    $fragment = "unaryOperation(".$operand.",preDec())";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }

  public function pprintPreIncExpr(PHPParser_Node_Expr_PreInc $node)
  {
    $operand = $this->pprint($node->var);
    $fragment = "unaryOperation(".$operand.",preInc())";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }

  public function pprintPrintExpr(PHPParser_Node_Expr_Print $node)
  {
    $operand = $this->pprint($node->expr);
    $fragment = "print(" . $operand . ")";
    $fragment .= $this->tagWithLine($node);
    return $fragment;
  }
	
  public function pprintPropertyFetchExpr(PHPParser_Node_Expr_PropertyFetch $node)
  {
    if ($node->name instanceof PHPParser_Node_Expr) {
      $fragment = "expr(" . $this->pprint($node->name) . ")";
    } else {
      $fragment = "name(name(\"" . $node->name . "\"))";
    }

    $fragment = "propertyFetch(" . $this->pprint($node->var) . "," . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintShellExecExpr(PHPParser_Node_Expr_ShellExec $node)
  {
    $parts = array();
    foreach($node->parts as $item) {
      if ($item instanceof PHPParser_Node_Expr) {
	$parts[] = $this->pprint($item);
      } else {
	$parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
      }
    }

    $fragment = "shellExec([" . implode(",",$parts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintShiftLeftExpr(PHPParser_Node_Expr_ShiftLeft $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",leftShift())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintShiftRightExpr(PHPParser_Node_Expr_ShiftRight $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",rightShift())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintSmallerExpr(PHPParser_Node_Expr_Smaller $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",lt())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintSmallerOrEqualExpr(PHPParser_Node_Expr_SmallerOrEqual $node)
  {
    $right = $this->pprint($node->right);
    $left = $this->pprint($node->left);

    $fragment = "binaryOperation(".$left.",".$right.",leq())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintStaticCallExpr(PHPParser_Node_Expr_StaticCall $node)
  {
    $args = array();
    foreach($node->args as $arg)
      $args[] = $this->pprint($arg);

    if ($node->name instanceof PHPParser_Node_Expr)
      $name = "expr(" . $this->pprint($node->name) . ")";
    else
      $name = "name(name(\"" . $node->name . "\"))";

    if ($node->class instanceof PHPParser_Node_Expr) {
      $class = "expr(" . $this->pprint($node->class) . ")";
    } else {
      $class = "name(" . $this->pprint($node->class) . ")";
    }

    $fragment = "staticCall({$class},{$name},[" . implode(",",$args) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintStaticPropertyFetchExpr(PHPParser_Node_Expr_StaticPropertyFetch $node)
  {
    if ($node->name instanceof PHPParser_Node_Expr) {
      $name = "expr(" . $this->pprint($node->name) . ")";
    } else {
      $name = "name(name(\"" . $node->name . "\"))";
    }

    if ($node->class instanceof PHPParser_Node_Expr) {
      $class = "expr(" . $this->pprint($node->class) . ")";
    } else {
      $class = "name(" . $this->pprint($node->class) . ")";
    }

    $fragment = "fetchStaticProperty({$class},{$name})";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintTernaryExpr(PHPParser_Node_Expr_Ternary $node)
  {
    $else = $this->pprint($node->else);
    if (null != $node->if)
      $if = "someExpr(" . $this->pprint($node->if) . ")";
    else
      $if = "noExpr()";
    $cond = $this->pprint($node->cond);
    
    $fragment = "ternary({$cond},{$if},{$else})";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintUnaryMinusExpr(PHPParser_Node_Expr_UnaryMinus $node)
  {
    $operand = $this->pprint($node->expr);
    $fragment = "unaryOperation(".$operand.",unaryMinus())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintUnaryPlusExpr(PHPParser_Node_Expr_UnaryPlus $node)
  {
    $operand = $this->pprint($node->expr);
    $fragment = "unaryOperation(".$operand.",unaryPlus())";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintVariableExpr(PHPParser_Node_Expr_Variable $node)
  {
    if ($node->name instanceof PHPParser_Node_Expr) {
      $fragment = "expr(" . $this->pprint($node->name) . ")";
    } else {
      $fragment = "name(name(\"" . $node->name . "\"))";
    }
    $fragment = "var(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintFullyQualifiedName(PHPParser_Node_Name_FullyQualified $node)
  {
    return $this->pprintName($node);
  }
	
  public function pprintRelativeName(PHPParser_Node_Name_Relative $node)
  {
    return $this->pprintName($node);
  }

  public function pprintName(PHPParser_Node_Name $node)
  {
    if (is_array($node->parts))
      $fragment = implode("::",$node->parts);
    else
      $fragment = $node->parts;
    $fragment = "name(\"" . $fragment . "\")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintParam(PHPParser_Node_Param $node)
  {
    if (null == $node->type) {
      $type = "noName()";
    } else {
      if ($node->type instanceof PHPParser_Node_Name) {
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

    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintClassConstScalar(PHPParser_Node_Scalar_ClassConst $node)
  {
    $fragment = "classConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintDirConstScalar(PHPParser_Node_Scalar_DirConst $node)
  {
    $fragment = "dirConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintDNumberScalar(PHPParser_Node_Scalar_DNumber $node)
  {
    $fragment = "float(" . sprintf('%f', $node->value) . ")";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintEncapsedScalar(PHPParser_Node_Scalar_Encapsed $node)
  {
    $parts = array();
    foreach($node->parts as $item) {
      if ($item instanceof PHPParser_Node_Expr) {
	$parts[] = $this->pprint($item);
      } else {
	$parts[] = "scalar(string(\"" . $this->rascalizeString($item) . "\"))";
      }
    }
    $fragment = "scalar(encapsed([" . implode(",",$parts) . "]))";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintFileConstScalar(PHPParser_Node_Scalar_FileConst $node)
  {
    $fragment = "fileConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintFuncConstScalar(PHPParser_Node_Scalar_FuncConst $node)
  {
    $fragment = "funcConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintLineConstScalar(PHPParser_Node_Scalar_LineConst $node)
  {
    $fragment = "lineConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintLNumberScalar(PHPParser_Node_Scalar_LNumber $node)
  {
    $fragment = "integer(" . sprintf('%d',$node->value) . ")";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintMethodConstScalar(PHPParser_Node_Scalar_MethodConst $node)
  {
    $fragment = "methodConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintNSConstScalar(PHPParser_Node_Scalar_NSConst $node)
  {
    $fragment = "namespaceConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintStringScalar(PHPParser_Node_Scalar_String $node)
  {
    $fragment = "string(\"" . $this->rascalizeString($node->value) . "\")";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintTraitConstScalar(PHPParser_Node_Scalar_TraitConst $node)
  {
    $fragment = "traitConstant()";
    $fragment = "scalar(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintBreakStmt(PHPParser_Node_Stmt_Break $node)
  {
    if (null != $node->num)
      $fragment = "someExpr(" . $this->pprint($node->num) . ")";
    else
      $fragment = "noExpr()";

    $fragment = "\\break(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintCaseStmt(PHPParser_Node_Stmt_Case $node)
  {
    if (null != $node->cond)
      $cond = "someExpr(" . $this->pprint($node->cond) . ")";
    else
      $cond = "noExpr()";

    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $fragment = "\\case(" . $cond . ",[" . implode(",",$body) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintCatchStmt(PHPParser_Node_Stmt_Catch $node)
  {
    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $xtype = $this->pprint($node->type);

    $fragment = "\\catch(" . $xtype . ",\"" . $this->rascalizeString($node->var) . "\",[" . implode(",",$body) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintClassStmt(PHPParser_Node_Stmt_Class $node)
  {
    $stmts = array();
    foreach($node->stmts as $stmt)
      $stmts[] = $this->pprint($stmt);

    $implements = array();
    foreach($node->implements as $implemented)
      $implements[] = $this->pprint($implemented);

    if (null != $node->extends)
      $extends = "someName(" . $this->pprint($node->extends) . ")";
    else
      $extends = "noName()";
    
    $modifiers = array();
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC) $modifiers[] = "\public()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PROTECTED) $modifiers[] = "protected()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE) $modifiers[] = "\private()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_ABSTRACT) $modifiers[] = "abstract()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_FINAL) $modifiers[] = "final()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_STATIC) $modifiers[] = "static()";

    $fragment = "class(\"" . $this->rascalizeString($node->name) . "\",{" . implode(",", $modifiers) . "}," . $extends . ",";
    $fragment .= "[" . implode(",",$implements) . "],[";
    $fragment .= implode(",",$stmts)."])";
    $fragment .= $this->tagWithLine($node);

    $fragment = "classDef(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintClassConstStmt(PHPParser_Node_Stmt_ClassConst $node)
  {
    $consts = array();
    foreach($node->consts as $const)
      $consts[] = $this->pprint($const);

    $fragment = "const([" . implode(",", $consts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintClassMethodStmt(PHPParser_Node_Stmt_ClassMethod $node)
  {
    $body = array();
    if (null != $node->stmts)
      foreach($node->stmts as $thestmt)
	$body[] = $this->pprint($thestmt);

    $params = array();
    foreach($node->params as $param)
      $params[] = $this->pprint($param);

    $modifiers = array();
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC) $modifiers[] = "\public()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PROTECTED) $modifiers[] = "protected()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE) $modifiers[] = "\private()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_ABSTRACT) $modifiers[] = "abstract()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_FINAL) $modifiers[] = "final()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_STATIC) $modifiers[] = "static()";

    $byRef = "false";
    if ($node->byRef) $byRef = "true";

    $fragment = "method(\"" . $node->name . "\",{" . implode(",",$modifiers) . "}," . $byRef . ",[" 
      . implode(",",$params) . "],[" . implode(",",$body) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintConstStmt(PHPParser_Node_Stmt_Const $node)
  {
    $consts = array();
    foreach($node->consts as $const)
      $consts[] = $this->pprint($const);

    $fragment = "const([" . implode(",", $consts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintContinueStmt(PHPParser_Node_Stmt_Continue $node)
  {
    if (null != $node->num)
      $fragment = "someExpr(" . $this->pprint($node->num) . ")";
    else
      $fragment = "noExpr()";

    $fragment = "\\continue(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintDeclareStmt(PHPParser_Node_Stmt_Declare $node)
  {
    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $decls = array();
    foreach($node->declares as $decl)
      $decls[] = $this->pprint($decl);

    $fragment = "declare([" . implode(",", $decls) . "],[" . implode(",", $body) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintDeclareDeclareStmt(PHPParser_Node_Stmt_DeclareDeclare $node)
  {
    $fragment = "declaration(\"" . $this->rascalizeString($node->key) . "\", " . $this->pprint($node->value) . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintDoStmt(PHPParser_Node_Stmt_Do $node)
  {
    $stmts = array();
    foreach($node->stmts as $stmt)
      $stmts[] = $this->pprint($stmt);

    $fragment = "\\do(" . $this->pprint($node->cond) . ",[" . implode(",",$stmts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintEchoStmt(PHPParser_Node_Stmt_Echo $node)
  {
    $parts = array();
    foreach($node->exprs as $expr)
      $parts[] = $this->pprint($expr);

    $fragment = "echo([" . implode(",", $parts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintElseStmt(PHPParser_Node_Stmt_Else $node)
  {
    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $fragment = "\\else([" . implode(",",$body) . "])"; 
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintElseIfStmt(PHPParser_Node_Stmt_ElseIf $node)
  {
    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);
    
    $fragment = "elseIf(" . $this->pprint($node->cond) . ",[" . implode(",",$body) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintExprStmt(PHPParser_Node_Stmt_Expr $node)
  {
    $fragment = "exprstmt(" . $this->pprint($node->expr) . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintForStmt(PHPParser_Node_Stmt_For $node)
  {
    $stmts = array();
    foreach($node->stmts as $stmt)
      $stmts[] = $this->pprint($stmt);
    
    $loops = array();
    foreach($node->loop as $loop)
      $loops[] = $this->pprint($loop);

    $conds = array();
    foreach($node->cond as $cond)
      $conds[] = $this->pprint($cond);

    $inits = array();
    foreach($node->init as $init)
      $inits[] = $this->pprint($init);

    $fragment = "\\for([" . implode(",", $inits) . "],[" . implode(",", $conds) . "],[" . implode(",", $loops) . "],[" . implode(",", $stmts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;    
  }
	
  public function pprintForeachStmt(PHPParser_Node_Stmt_Foreach $node)
  {
    $valueVar = $this->pprint($node->valueVar);
    $expr = $this->pprint($node->expr);
    $byRef = "false"; if ($node->byRef) $byRef = "true";

    $stmts = array();
    foreach($node->stmts as $stmt)
      $stmts[] = $this->pprint($stmt);

    $keyvar = "noExpr()";
    if (null != $node->keyVar)
      $keyvar = "someExpr(" . $this->pprint($node->keyVar) . ")";

    $fragment = "foreach(" . $expr . "," . $keyvar . "," . $byRef . "," . $valueVar . ",[" 
      . implode(",",$stmts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintFunctionStmt(PHPParser_Node_Stmt_Function $node)
  {
    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $params = array();
    foreach($node->params as $param)
      $params[] = $this->pprint($param);

    $byRef = "false";
    if ($node->byRef) $byRef = "true";

    $fragment = "function(\"" . $this->rascalizeString($node->name) . "\"," . $byRef 
      . ",[" . implode(",",$params) . "],[" . implode(",",$body) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintGlobalStmt(PHPParser_Node_Stmt_Global $node)
  {
    $vars = array();
    foreach($node->vars as $var)
      $vars[] = $this->pprint($var);
    
    $fragment = "global([" . implode(",",$vars) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintGotoStmt(PHPParser_Node_Stmt_Goto $node)
  {
    $fragment = "goto(\"" . $this->rascalizeString($node->name) . "\")"; 
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintHaltCompilerStmt(PHPParser_Node_Stmt_HaltCompiler $node)
  {
    $fragment = "haltCompiler(\"" . $this->rascalizeString($node->remaining) . "\")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintIfStmt(PHPParser_Node_Stmt_If $node)
  {
    $cond = $this->pprint($node->cond);

    if (null != $node->else)
      $elseNode = "someElse(" . $this->pprint($node->else) . ")";
    else
      $elseNode = "noElse()";

    $elseIfs = array();
    foreach($node->elseifs as $elseif)
      $elseIfs[] = $this->pprint($elseif);

    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $fragment = "\\if(" . $cond . ",[" . implode(",", $body) . "],[" 
      . implode(",", $elseIfs) . "]," . $elseNode . ")";
    $fragment .= $this->tagWithLine($node);
    
    return $fragment;
  }

  public function pprintInlineHTMLStmt(PHPParser_Node_Stmt_InlineHTML $node)
  {
    $fragment = "inlineHTML(\"".$this->rascalizeString($node->value)."\")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintInterfaceStmt(PHPParser_Node_Stmt_Interface $node)
  {
    $stmts = array();
    foreach($node->stmts as $stmt)
      $stmts[] = $this->pprint($stmt);

    $extends = array();
    foreach($node->extends as $extended)
      $extends[] = $this->pprint($extended);
    
    $fragment = "interface(\"" . $this->rascalizeString($node->name) . "\",[";
    $fragment .= implode(",",$extends) . "],[";
    $fragment .= implode(",",$stmts) . "])";
    $fragment .= $this->tagWithLine($node);

    $fragment = "interfaceDef(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintLabelStmt(PHPParser_Node_Stmt_Label $node)
  {
    $fragment = "label(\"" . $this->rascalizeString($node->name) . "\")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintNamespaceStmt(PHPParser_Node_Stmt_Namespace $node)
  {
    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    if (null != $node->name)
      $name = "someName(" . $this->pprint($node->name) . ")";
    else
      $name = "noName()";

    $fragment = "namespace(" . $name . ",[" . implode(",",$body) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintPropertyStmt(PHPParser_Node_Stmt_Property $node)
  {
    $props = array();
    foreach($node->props as $prop)
      $props[] = $this->pprint($prop);

    $modifiers = array();
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC) $modifiers[] = "\public()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PROTECTED) $modifiers[] = "protected()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE) $modifiers[] = "\private()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_ABSTRACT) $modifiers[] = "abstract()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_FINAL) $modifiers[] = "final()";
    if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_STATIC) $modifiers[] = "static()";

    $fragment = "property({" . implode(",",$modifiers) . "},[" . implode(",",$props) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
  
  public function pprintPropertyPropertyStmt(PHPParser_Node_Stmt_PropertyProperty $node)
  {
    if (null != $node->default) {
      $fragment = "someExpr(" . $this->pprint($node->default) . ")";
    } else {
      $fragment = "noExpr()";
    }

    $fragment = "property(\"" . $node->name . "\"," . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintReturnStmt(PHPParser_Node_Stmt_Return $node)
  {
    if (null != $node->expr)
      $fragment = "someExpr(" . $this->pprint($node->expr) . ")";
    else
      $fragment = "noExpr()";
    $fragment = "\\return(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintStaticStmt(PHPParser_Node_Stmt_Static $node)
  {
    $staticVars = array();
    foreach($node->vars as $var)
      $staticVars[] = $this->pprint($var);

    $fragment = "static([" . implode(",", $staticVars) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintStaticVarStmt(PHPParser_Node_Stmt_StaticVar $node)
  {
    $default = "noExpr()";
    if (null != $node->default)
      $default = "someExpr(" . $this->pprint($node->default) . ")";

    $fragment = "staticVar(\"" . $this->rascalizeString($node->name) . "\"," . $default . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintSwitchStmt(PHPParser_Node_Stmt_Switch $node)
  {
    $cases = array();
    foreach($node->cases as $case)
      $cases[] = $this->pprint($case);

    $fragment = "\\switch(" . $this->pprint($node->cond) . ",[" . implode(",",$cases) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintThrowStmt(PHPParser_Node_Stmt_Throw $node)
  {
    $fragment = "\\throw(" . $this->pprint($node->expr) . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintTraitStmt(PHPParser_Node_Stmt_Trait $node)
  {
    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $fragment = "trait(\"" . $node->name . "\",[" . implode(",",$body) . "])";
    $fragment .= $this->tagWithLine($node);

    $fragment = "traitDef(" . $fragment . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintTraitUseStmt(PHPParser_Node_Stmt_TraitUse $node)
  {
    $adaptations = array();
    foreach($node->adaptations as $adaptation)
      $adaptations[] = $this->pprint($adaptation);

    $traits = array();
    foreach($node->traits as $trait)
      $traits[] = $this->pprint($trait);

    $fragment = "traitUse([" . implode(",",$traits) . "],[" . implode(",",$adaptations) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintAliasTraitUseAdaptationStmt(PHPParser_Node_Stmt_TraitUseAdaptation_Alias $node)
  {
    if (null != $node->newName) {
      $newName = "someName(name(\"" . $node->newName . "\"))";
    } else {
      $newName = "noName()";
    }

    if (null != $node->newModifier) {
      $modifiers = array();
      if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PUBLIC) $modifiers[] = "\public()";
      if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PROTECTED) $modifiers[] = "protected()";
      if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE) $modifiers[] = "\private()";
      if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_ABSTRACT) $modifiers[] = "abstract()";
      if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_FINAL) $modifiers[] = "final()";
      if ($node->type & PHPParser_Node_Stmt_Class::MODIFIER_STATIC) $modifiers[] = "static()";
      $newModifier = "{ " . implode(",",$modifiers) . " }";
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
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
	
  public function pprintPrecedenceTraitUseAdaptationStmt(PHPParser_Node_Stmt_TraitUseAdaptation_Precedence $node)
  {
    $insteadOf = array();
    foreach($node->insteadof as $item)
      $insteadOf[] = $this->pprint($item);

    $fragment = "traitPrecedence(" . $this->pprint($node->trait) . ",\"" . $node->method . "\",[" . implode(",",$insteadOf) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintTryCatchStmt(PHPParser_Node_Stmt_TryCatch $node)
  {
    $catches = array();
    foreach($node->catches as $toCatch)
      $catches[] = $this->pprint($toCatch);

    $body = array();
    foreach($node->stmts as $stmt)
      $body[] = $this->pprint($stmt);

    $fragment = "tryCatch([" . implode(",", $body) . "],[" . implode(",",$catches) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintUnsetStmt(PHPParser_Node_Stmt_Unset $node)
  {
    $vars = array();
    foreach($node->vars as $var)
      $vars[] = $this->pprint($var);

    $fragment = "unset([" . implode(",", $vars) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintUseStmt(PHPParser_Node_Stmt_Use $node)
  {
    $uses = array();
    foreach($node->uses as $use)
      $uses[] = $this->pprint($use);

    $fragment = "use([" . implode(",", $uses) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }

  public function pprintUseUseStmt(PHPParser_Node_Stmt_UseUse $node)
  {
    $name = $this->pprint($node->name);
    if (null != $node->alias)
      $alias = "someName(name(\"" . $node->alias . "\"))";
    else
      $alias = "noName()";

    $fragment = "use(" . $name . "," . $alias . ")";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
  
  public function pprintWhileStmt(PHPParser_Node_Stmt_While $node)
  {
    $stmts = array();
    foreach($node->stmts as $stmt) 
      $stmts[] = $this->pprint($stmt);

    $fragment = "\\while(" . $this->pprint($node->cond) . ",[" . implode(",",$stmts) . "])";
    $fragment .= $this->tagWithLine($node);

    return $fragment;
  }
}

if (count($argv) != 2) {
  echo "Expected exactly 1 argument\n";
  exit -1;
}

$file = $argv[1];

$inputCode = '';
if (file_exists($file))
  $inputCode = file_get_contents($file);

$parser = new PHPParser_Parser;
$dumper = new PHPParser_NodeDumper;
$printer = new AST2Rascal($file);

try {
  $stmts = $parser->parse(new PHPParser_Lexer($inputCode));
/*   echo htmlspecialchars($dumper->dump($stmts)); */
  $strStmts = array();
  foreach($stmts as $stmt) $strStmts[] = $printer->pprint($stmt);
  $script = implode(",\n", $strStmts);
  echo "script([" . $script . "])";
} catch (PHPParser_Error $e) {
  echo 'Parse Error: ', $e->getMessage();
}
?>
