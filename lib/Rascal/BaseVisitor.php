<?php

namespace Rascal;

class BaseVisitor implements IVisitor
{
	public function enterArg(PhpParser_Node_Arg $node)
	{
		return null;
	}
	public function enterConst(PhpParser_Node_Const $node)
	{
		return null;
	}
	public function enterArrayExpr(PhpParser_Node_Expr_Array $node)
	{
		return null;
	}
	public function enterArrayDimFetchExpr(PhpParser_Node_Expr_ArrayDimFetch $node)
	{
		return null;
	}
	public function enterArrayItemExpr(PhpParser_Node_Expr_ArrayItem $node)
	{
		return null;
	}
	public function enterAssignExpr(PhpParser_Node_Expr_Assign $node)
	{
		return null;
	}
	public function enterAssignBitwiseAndExpr(PhpParser_Node_Expr_AssignBitwiseAnd $node)
	{
		return null;
	}
	public function enterAssignBitwiseOrExpr(PhpParser_Node_Expr_AssignBitwiseOr $node)
	{
		return null;
	}
	public function enterAssignBitwiseXorExpr(PhpParser_Node_Expr_AssignBitwiseXor $node)
	{
		return null;
	}
	public function enterAssignConcatExpr(PhpParser_Node_Expr_AssignConcat $node)
	{
		return null;
	}
	public function enterAssignDivExpr(PhpParser_Node_Expr_AssignDiv $node)
	{
		return null;
	}
	public function enterAssignMinusExpr(PhpParser_Node_Expr_AssignMinus $node)
	{
		return null;
	}
	public function enterAssignModExpr(PhpParser_Node_Expr_AssignMod $node)
	{
		return null;
	}
	public function enterAssignMulExpr(PhpParser_Node_Expr_AssignMul $node)
	{
		return null;
	}
	public function enterAssignPlusExpr(PhpParser_Node_Expr_AssignPlus $node)
	{
		return null;
	}
	public function enterAssignRefExpr(PhpParser_Node_Expr_AssignRef $node)
	{
		return null;
	}
	public function enterAssignShiftLeftExpr(PhpParser_Node_Expr_AssignShiftLeft $node)
	{
		return null;
	}
	public function enterAssignShiftRightExpr(PhpParser_Node_Expr_AssignShiftRight $node)
	{
		return null;
	}
	public function enterBitwiseAndExpr(PhpParser_Node_Expr_BitwiseAnd $node)
	{
		return null;
	}
	public function enterBitwiseNotExpr(PhpParser_Node_Expr_BitwiseNot $node)
	{
		return null;
	}
	public function enterBitwiseOrExpr(PhpParser_Node_Expr_BitwiseOr $node)
	{
		return null;
	}
	public function enterBitwiseXorExpr(PhpParser_Node_Expr_BitwiseXor $node)
	{
		return null;
	}
	public function enterBooleanAndExpr(PhpParser_Node_Expr_BooleanAnd $node)
	{
		return null;
	}
	public function enterBooleanNotExpr(PhpParser_Node_Expr_BooleanNot $node)
	{
		return null;
	}
	public function enterBooleanOrExpr(PhpParser_Node_Expr_BooleanOr $node)
	{
		return null;
	}
	public function enterArrayCastExpr(PhpParser_Node_Expr_Cast_Array $node)
	{
		return null;
	}
	public function enterBoolCastExpr(PhpParser_Node_Expr_Cast_Bool $node)
	{
		return null;
	}
	public function enterDoubleCastExpr(PhpParser_Node_Expr_Cast_Double $node)
	{
		return null;
	}
	public function enterIntCastExpr(PhpParser_Node_Expr_Cast_Int $node)
	{
		return null;
	}
	public function enterObjectCastExpr(PhpParser_Node_Expr_Cast_Object $node)
	{
		return null;
	}
	public function enterStringCastExpr(PhpParser_Node_Expr_Cast_String $node)
	{
		return null;
	}
	public function enterUnsetCastExpr(PhpParser_Node_Expr_Cast_Unset $node)
	{
		return null;
	}
	public function enterCastExpr(PhpParser_Node_Expr_Cast $node)
	{
		return null;
	}
	public function enterClassConstFetchExpr(PhpParser_Node_Expr_ClassConstFetch $node)
	{
		return null;
	}
	public function enterCloneExpr(PhpParser_Node_Expr_Clone $node)
	{
		return null;
	}
	public function enterClosureExpr(PhpParser_Node_Expr_Closure $node)
	{
		return null;
	}
	public function enterClosureUseExpr(PhpParser_Node_Expr_ClosureUse $node)
	{
		return null;
	}
	public function enterConcatExpr(PhpParser_Node_Expr_Concat $node)
	{
		return null;
	}
	public function enterConstFetchExpr(PhpParser_Node_Expr_ConstFetch $node)
	{
		return null;
	}
	public function enterDivExpr(PhpParser_Node_Expr_Div $node)
	{
		return null;
	}
	public function enterEmptyExpr(PhpParser_Node_Expr_Empty $node)
	{
		return null;
	}
	public function enterEqualExpr(PhpParser_Node_Expr_Equal $node)
	{
		return null;
	}
	public function enterErrorSuppressExpr(PhpParser_Node_Expr_ErrorSuppress $node)
	{
		return null;
	}
	public function enterEvalExpr(PhpParser_Node_Expr_Eval $node)
	{
		return null;
	}
	public function enterExitExpr(PhpParser_Node_Expr_Exit $node)
	{
		return null;
	}
	public function enterFuncCallExpr(PhpParser_Node_Expr_FuncCall $node)
	{
		return null;
	}
	public function enterGreaterExpr(PhpParser_Node_Expr_Greater $node)
	{
		return null;
	}
	public function enterGreaterOrEqualExpr(PhpParser_Node_Expr_GreaterOrEqual $node)
	{
		return null;
	}
	public function enterIdenticalExpr(PhpParser_Node_Expr_Identical $node)
	{
		return null;
	}
	public function enterIncludeExpr(PhpParser_Node_Expr_Include $node)
	{
		return null;
	}
	public function enterInstanceofExpr(PhpParser_Node_Expr_Instanceof $node)
	{
		return null;
	}
	public function enterIssetExpr(PhpParser_Node_Expr_Isset $node)
	{
		return null;
	}
	public function enterListExpr(PhpParser_Node_Expr_List $node)
	{
		return null;
	}
	public function enterLogicalAndExpr(PhpParser_Node_Expr_LogicalAnd $node)
	{
		return null;
	}
	public function enterLogicalOrExpr(PhpParser_Node_Expr_LogicalOr $node)
	{
		return null;
	}
	public function enterLogicalXorExpr(PhpParser_Node_Expr_LogicalXor $node)
	{
		return null;
	}
	public function enterMethodCallExpr(PhpParser_Node_Expr_MethodCall $node)
	{
		return null;
	}
	public function enterMinusExpr(PhpParser_Node_Expr_Minus $node)
	{
		return null;
	}
	public function enterModExpr(PhpParser_Node_Expr_Mod $node)
	{
		return null;
	}
	public function enterMulExpr(PhpParser_Node_Expr_Mul $node)
	{
		return null;
	}
	public function enterNewExpr(PhpParser_Node_Expr_New $node)
	{
		return null;
	}
	public function enterNotEqualExpr(PhpParser_Node_Expr_NotEqual $node)
	{
		return null;
	}
	public function enterNotIdenticalExpr(PhpParser_Node_Expr_NotIdentical $node)
	{
		return null;
	}
	public function enterPlusExpr(PhpParser_Node_Expr_Plus $node)
	{
		return null;
	}
	public function enterPostDecExpr(PhpParser_Node_Expr_PostDec $node)
	{
		return null;
	}
	public function enterPostIncExpr(PhpParser_Node_Expr_PostInc $node)
	{
		return null;
	}
	public function enterPreDecExpr(PhpParser_Node_Expr_PreDec $node)
	{
		return null;
	}
	public function enterPreIncExpr(PhpParser_Node_Expr_PreInc $node)
	{
		return null;
	}
	public function enterPrintExpr(PhpParser_Node_Expr_Print $node)
	{
		return null;
	}
	public function enterPropertyFetchExpr(PhpParser_Node_Expr_PropertyFetch $node)
	{
		return null;
	}
	public function enterShellExecExpr(PhpParser_Node_Expr_ShellExec $node)
	{
		return null;
	}
	public function enterShiftLeftExpr(PhpParser_Node_Expr_ShiftLeft $node)
	{
		return null;
	}
	public function enterShiftRightExpr(PhpParser_Node_Expr_ShiftRight $node)
	{
		return null;
	}
	public function enterSmallerExpr(PhpParser_Node_Expr_Smaller $node)
	{
		return null;
	}
	public function enterSmallerOrEqualExpr(PhpParser_Node_Expr_SmallerOrEqual $node)
	{
		return null;
	}
	public function enterStaticCallExpr(PhpParser_Node_Expr_StaticCall $node)
	{
		return null;
	}
	public function enterStaticPropertyFetchExpr(PhpParser_Node_Expr_StaticPropertyFetch $node)
	{
		return null;
	}
	public function enterTernaryExpr(PhpParser_Node_Expr_Ternary $node)
	{
		return null;
	}
	public function enterUnaryMinusExpr(PhpParser_Node_Expr_UnaryMinus $node)
	{
		return null;
	}
	public function enterUnaryPlusExpr(PhpParser_Node_Expr_UnaryPlus $node)
	{
		return null;
	}
	public function enterVariableExpr(PhpParser_Node_Expr_Variable $node)
	{
		return null;
	}
	public function enterYieldExpr(PhpParser_Node_Expr_Yield $node)
	{
		return null;
	}
	public function enterFullyQualifiedName(PhpParser_Node_Name_FullyQualified $node)
	{
		return null;
	}
	public function enterRelativeName(PhpParser_Node_Name_Relative $node)
	{
		return null;
	}
	public function enterName(PhpParser_Node_Name $node)
	{
		return null;
	}
	public function enterParam(PhpParser_Node_Param $node)
	{
		return null;
	}
	public function enterClassConstScalar(PhpParser_Node_Scalar_ClassConst $node)
	{
		return null;
	}
	public function enterDirConstScalar(PhpParser_Node_Scalar_DirConst $node)
	{
		return null;
	}
	public function enterDNumberScalar(PhpParser_Node_Scalar_DNumber $node)
	{
		return null;
	}
	public function enterEncapsedScalar(PhpParser_Node_Scalar_Encapsed $node)
	{
		return null;
	}
	public function enterFileConstScalar(PhpParser_Node_Scalar_FileConst $node)
	{
		return null;
	}
	public function enterFuncConstScalar(PhpParser_Node_Scalar_FuncConst $node)
	{
		return null;
	}
	public function enterLineConstScalar(PhpParser_Node_Scalar_LineConst $node)
	{
		return null;
	}
	public function enterLNumberScalar(PhpParser_Node_Scalar_LNumber $node)
	{
		return null;
	}
	public function enterMethodConstScalar(PhpParser_Node_Scalar_MethodConst $node)
	{
		return null;
	}
	public function enterNSConstScalar(PhpParser_Node_Scalar_NSConst $node)
	{
		return null;
	}
	public function enterStringScalar(PhpParser_Node_Scalar_String $node)
	{
		return null;
	}
	public function enterTraitConstScalar(PhpParser_Node_Scalar_TraitConst $node)
	{
		return null;
	}
	public function enterScalar(PhpParser_Node_Scalar $node)
	{
		return null;
	}
	public function enterBreakStmt(PhpParser_Node_Stmt_Break $node)
	{
		return null;
	}
	public function enterCaseStmt(PhpParser_Node_Stmt_Case $node)
	{
		return null;
	}
	public function enterCatchStmt(PhpParser_Node_Stmt_Catch $node)
	{
		return null;
	}
	public function enterClassStmt(PhpParser_Node_Stmt_Class $node)
	{
		return null;
	}
	public function enterClassConstStmt(PhpParser_Node_Stmt_ClassConst $node)
	{
		return null;
	}
	public function enterClassMethodStmt(PhpParser_Node_Stmt_ClassMethod $node)
	{
		return null;
	}
	public function enterConstStmt(PhpParser_Node_Stmt_Const $node)
	{
		return null;
	}
	public function enterContinueStmt(PhpParser_Node_Stmt_Continue $node)
	{
		return null;
	}
	public function enterDeclareStmt(PhpParser_Node_Stmt_Declare $node)
	{
		return null;
	}
	public function enterDeclareDeclareStmt(PhpParser_Node_Stmt_DeclareDeclare $node)
	{
		return null;
	}
	public function enterDoStmt(PhpParser_Node_Stmt_Do $node)
	{
		return null;
	}
	public function enterEchoStmt(PhpParser_Node_Stmt_Echo $node)
	{
		return null;
	}
	public function enterElseStmt(PhpParser_Node_Stmt_Else $node)
	{
		return null;
	}
	public function enterElseIfStmt(PhpParser_Node_Stmt_ElseIf $node)
	{
		return null;
	}
	public function enterExprStmt(PhpParser_Node_Stmt_Expr $node)
	{
		return null;
	}
	public function enterForStmt(PhpParser_Node_Stmt_For $node)
	{
		return null;
	}
	public function enterForeachStmt(PhpParser_Node_Stmt_Foreach $node)
	{
		return null;
	}
	public function enterFunctionStmt(PhpParser_Node_Stmt_Function $node)
	{
		return null;
	}
	public function enterGlobalStmt(PhpParser_Node_Stmt_Global $node)
	{
		return null;
	}
	public function enterGotoStmt(PhpParser_Node_Stmt_Goto $node)
	{
		return null;
	}
	public function enterHaltCompilerStmt(PhpParser_Node_Stmt_HaltCompiler $node)
	{
		return null;
	}
	public function enterIfStmt(PhpParser_Node_Stmt_If $node)
	{
		return null;
	}
	public function enterInlineHTMLStmt(PhpParser_Node_Stmt_InlineHTML $node)
	{
		return null;
	}
	public function enterInterfaceStmt(PhpParser_Node_Stmt_Interface $node)
	{
		return null;
	}
	public function enterLabelStmt(PhpParser_Node_Stmt_Label $node)
	{
		return null;
	}
	public function enterNamespaceStmt(PhpParser_Node_Stmt_Namespace $node)
	{
		return null;
	}
	public function enterPropertyStmt(PhpParser_Node_Stmt_Property $node)
	{
		return null;
	}
	public function enterPropertyPropertyStmt(PhpParser_Node_Stmt_PropertyProperty $node)
	{
		return null;
	}
	public function enterReturnStmt(PhpParser_Node_Stmt_Return $node)
	{
		return null;
	}
	public function enterStaticStmt(PhpParser_Node_Stmt_Static $node)
	{
		return null;
	}
	public function enterStaticVarStmt(PhpParser_Node_Stmt_StaticVar $node)
	{
		return null;
	}
	public function enterSwitchStmt(PhpParser_Node_Stmt_Switch $node)
	{
		return null;
	}
	public function enterThrowStmt(PhpParser_Node_Stmt_Throw $node)
	{
		return null;
	}
	public function enterTraitStmt(PhpParser_Node_Stmt_Trait $node)
	{
		return null;
	}
	public function enterTraitUseStmt(PhpParser_Node_Stmt_TraitUse $node)
	{
		return null;
	}
	public function enterAliasTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation_Alias $node)
	{
		return null;
	}
	public function enterPrecedenceTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation_Precedence $node)
	{
		return null;
	}
	public function enterTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation $node)
	{
		return null;
	}
	public function enterTryCatchStmt(PhpParser_Node_Stmt_TryCatch $node)
	{
		return null;
	}
	public function enterUnsetStmt(PhpParser_Node_Stmt_Unset $node)
	{
		return null;
	}
	public function enterUseStmt(PhpParser_Node_Stmt_Use $node)
	{
		return null;
	}
	public function enterUseUseStmt(PhpParser_Node_Stmt_UseUse $node)
	{
		return null;
	}
	public function enterWhileStmt(PhpParser_Node_Stmt_While $node)
	{
		return null;
	}
	public function enterStmt(PhpParser_Node_Stmt $node)
	{
		return null;
	}
	public function enterExpr(PhpParser_Node_Expr $node)
	{
		return null;
	}

	public function leaveArg(PhpParser_Node_Arg $node)
	{
		return null;
	}
	public function leaveConst(PhpParser_Node_Const $node)
	{
		return null;
	}
	public function leaveArrayExpr(PhpParser_Node_Expr_Array $node)
	{
		return null;
	}
	public function leaveArrayDimFetchExpr(PhpParser_Node_Expr_ArrayDimFetch $node)
	{
		return null;
	}
	public function leaveArrayItemExpr(PhpParser_Node_Expr_ArrayItem $node)
	{
		return null;
	}
	public function leaveAssignExpr(PhpParser_Node_Expr_Assign $node)
	{
		return null;
	}
	public function leaveAssignBitwiseAndExpr(PhpParser_Node_Expr_AssignBitwiseAnd $node)
	{
		return null;
	}
	public function leaveAssignBitwiseOrExpr(PhpParser_Node_Expr_AssignBitwiseOr $node)
	{
		return null;
	}
	public function leaveAssignBitwiseXorExpr(PhpParser_Node_Expr_AssignBitwiseXor $node)
	{
		return null;
	}
	public function leaveAssignConcatExpr(PhpParser_Node_Expr_AssignConcat $node)
	{
		return null;
	}
	public function leaveAssignDivExpr(PhpParser_Node_Expr_AssignDiv $node)
	{
		return null;
	}
	public function leaveAssignMinusExpr(PhpParser_Node_Expr_AssignMinus $node)
	{
		return null;
	}
	public function leaveAssignModExpr(PhpParser_Node_Expr_AssignMod $node)
	{
		return null;
	}
	public function leaveAssignMulExpr(PhpParser_Node_Expr_AssignMul $node)
	{
		return null;
	}
	public function leaveAssignPlusExpr(PhpParser_Node_Expr_AssignPlus $node)
	{
		return null;
	}
	public function leaveAssignRefExpr(PhpParser_Node_Expr_AssignRef $node)
	{
		return null;
	}
	public function leaveAssignShiftLeftExpr(PhpParser_Node_Expr_AssignShiftLeft $node)
	{
		return null;
	}
	public function leaveAssignShiftRightExpr(PhpParser_Node_Expr_AssignShiftRight $node)
	{
		return null;
	}
	public function leaveBitwiseAndExpr(PhpParser_Node_Expr_BitwiseAnd $node)
	{
		return null;
	}
	public function leaveBitwiseNotExpr(PhpParser_Node_Expr_BitwiseNot $node)
	{
		return null;
	}
	public function leaveBitwiseOrExpr(PhpParser_Node_Expr_BitwiseOr $node)
	{
		return null;
	}
	public function leaveBitwiseXorExpr(PhpParser_Node_Expr_BitwiseXor $node)
	{
		return null;
	}
	public function leaveBooleanAndExpr(PhpParser_Node_Expr_BooleanAnd $node)
	{
		return null;
	}
	public function leaveBooleanNotExpr(PhpParser_Node_Expr_BooleanNot $node)
	{
		return null;
	}
	public function leaveBooleanOrExpr(PhpParser_Node_Expr_BooleanOr $node)
	{
		return null;
	}
	public function leaveArrayCastExpr(PhpParser_Node_Expr_Cast_Array $node)
	{
		return null;
	}
	public function leaveBoolCastExpr(PhpParser_Node_Expr_Cast_Bool $node)
	{
		return null;
	}
	public function leaveDoubleCastExpr(PhpParser_Node_Expr_Cast_Double $node)
	{
		return null;
	}
	public function leaveIntCastExpr(PhpParser_Node_Expr_Cast_Int $node)
	{
		return null;
	}
	public function leaveObjectCastExpr(PhpParser_Node_Expr_Cast_Object $node)
	{
		return null;
	}
	public function leaveStringCastExpr(PhpParser_Node_Expr_Cast_String $node)
	{
		return null;
	}
	public function leaveUnsetCastExpr(PhpParser_Node_Expr_Cast_Unset $node)
	{
		return null;
	}
	public function leaveCastExpr(PhpParser_Node_Expr_Cast $node)
	{
		return null;
	}
	public function leaveClassConstFetchExpr(PhpParser_Node_Expr_ClassConstFetch $node)
	{
		return null;
	}
	public function leaveCloneExpr(PhpParser_Node_Expr_Clone $node)
	{
		return null;
	}
	public function leaveClosureExpr(PhpParser_Node_Expr_Closure $node)
	{
		return null;
	}
	public function leaveClosureUseExpr(PhpParser_Node_Expr_ClosureUse $node)
	{
		return null;
	}
	public function leaveConcatExpr(PhpParser_Node_Expr_Concat $node)
	{
		return null;
	}
	public function leaveConstFetchExpr(PhpParser_Node_Expr_ConstFetch $node)
	{
		return null;
	}
	public function leaveDivExpr(PhpParser_Node_Expr_Div $node)
	{
		return null;
	}
	public function leaveEmptyExpr(PhpParser_Node_Expr_Empty $node)
	{
		return null;
	}
	public function leaveEqualExpr(PhpParser_Node_Expr_Equal $node)
	{
		return null;
	}
	public function leaveErrorSuppressExpr(PhpParser_Node_Expr_ErrorSuppress $node)
	{
		return null;
	}
	public function leaveEvalExpr(PhpParser_Node_Expr_Eval $node)
	{
		return null;
	}
	public function leaveExitExpr(PhpParser_Node_Expr_Exit $node)
	{
		return null;
	}
	public function leaveFuncCallExpr(PhpParser_Node_Expr_FuncCall $node)
	{
		return null;
	}
	public function leaveGreaterExpr(PhpParser_Node_Expr_Greater $node)
	{
		return null;
	}
	public function leaveGreaterOrEqualExpr(PhpParser_Node_Expr_GreaterOrEqual $node)
	{
		return null;
	}
	public function leaveIdenticalExpr(PhpParser_Node_Expr_Identical $node)
	{
		return null;
	}
	public function leaveIncludeExpr(PhpParser_Node_Expr_Include $node)
	{
		return null;
	}
	public function leaveInstanceofExpr(PhpParser_Node_Expr_Instanceof $node)
	{
		return null;
	}
	public function leaveIssetExpr(PhpParser_Node_Expr_Isset $node)
	{
		return null;
	}
	public function leaveListExpr(PhpParser_Node_Expr_List $node)
	{
		return null;
	}
	public function leaveLogicalAndExpr(PhpParser_Node_Expr_LogicalAnd $node)
	{
		return null;
	}
	public function leaveLogicalOrExpr(PhpParser_Node_Expr_LogicalOr $node)
	{
		return null;
	}
	public function leaveLogicalXorExpr(PhpParser_Node_Expr_LogicalXor $node)
	{
		return null;
	}
	public function leaveMethodCallExpr(PhpParser_Node_Expr_MethodCall $node)
	{
		return null;
	}
	public function leaveMinusExpr(PhpParser_Node_Expr_Minus $node)
	{
		return null;
	}
	public function leaveModExpr(PhpParser_Node_Expr_Mod $node)
	{
		return null;
	}
	public function leaveMulExpr(PhpParser_Node_Expr_Mul $node)
	{
		return null;
	}
	public function leaveNewExpr(PhpParser_Node_Expr_New $node)
	{
		return null;
	}
	public function leaveNotEqualExpr(PhpParser_Node_Expr_NotEqual $node)
	{
		return null;
	}
	public function leaveNotIdenticalExpr(PhpParser_Node_Expr_NotIdentical $node)
	{
		return null;
	}
	public function leavePlusExpr(PhpParser_Node_Expr_Plus $node)
	{
		return null;
	}
	public function leavePostDecExpr(PhpParser_Node_Expr_PostDec $node)
	{
		return null;
	}
	public function leavePostIncExpr(PhpParser_Node_Expr_PostInc $node)
	{
		return null;
	}
	public function leavePreDecExpr(PhpParser_Node_Expr_PreDec $node)
	{
		return null;
	}
	public function leavePreIncExpr(PhpParser_Node_Expr_PreInc $node)
	{
		return null;
	}
	public function leavePrintExpr(PhpParser_Node_Expr_Print $node)
	{
		return null;
	}
	public function leavePropertyFetchExpr(PhpParser_Node_Expr_PropertyFetch $node)
	{
		return null;
	}
	public function leaveShellExecExpr(PhpParser_Node_Expr_ShellExec $node)
	{
		return null;
	}
	public function leaveShiftLeftExpr(PhpParser_Node_Expr_ShiftLeft $node)
	{
		return null;
	}
	public function leaveShiftRightExpr(PhpParser_Node_Expr_ShiftRight $node)
	{
		return null;
	}
	public function leaveSmallerExpr(PhpParser_Node_Expr_Smaller $node)
	{
		return null;
	}
	public function leaveSmallerOrEqualExpr(PhpParser_Node_Expr_SmallerOrEqual $node)
	{
		return null;
	}
	public function leaveStaticCallExpr(PhpParser_Node_Expr_StaticCall $node)
	{
		return null;
	}
	public function leaveStaticPropertyFetchExpr(PhpParser_Node_Expr_StaticPropertyFetch $node)
	{
		return null;
	}
	public function leaveTernaryExpr(PhpParser_Node_Expr_Ternary $node)
	{
		return null;
	}
	public function leaveUnaryMinusExpr(PhpParser_Node_Expr_UnaryMinus $node)
	{
		return null;
	}
	public function leaveUnaryPlusExpr(PhpParser_Node_Expr_UnaryPlus $node)
	{
		return null;
	}
	public function leaveVariableExpr(PhpParser_Node_Expr_Variable $node)
	{
		return null;
	}
	public function leaveYieldExpr(PhpParser_Node_Expr_Yield $node)
	{
		return null;
	}
	public function leaveFullyQualifiedName(PhpParser_Node_Name_FullyQualified $node)
	{
		return null;
	}
	public function leaveRelativeName(PhpParser_Node_Name_Relative $node)
	{
		return null;
	}
	public function leaveName(PhpParser_Node_Name $node)
	{
		return null;
	}
	public function leaveParam(PhpParser_Node_Param $node)
	{
		return null;
	}
	public function leaveClassConstScalar(PhpParser_Node_Scalar_ClassConst $node)
	{
		return null;
	}
	public function leaveDirConstScalar(PhpParser_Node_Scalar_DirConst $node)
	{
		return null;
	}
	public function leaveDNumberScalar(PhpParser_Node_Scalar_DNumber $node)
	{
		return null;
	}
	public function leaveEncapsedScalar(PhpParser_Node_Scalar_Encapsed $node)
	{
		return null;
	}
	public function leaveFileConstScalar(PhpParser_Node_Scalar_FileConst $node)
	{
		return null;
	}
	public function leaveFuncConstScalar(PhpParser_Node_Scalar_FuncConst $node)
	{
		return null;
	}
	public function leaveLineConstScalar(PhpParser_Node_Scalar_LineConst $node)
	{
		return null;
	}
	public function leaveLNumberScalar(PhpParser_Node_Scalar_LNumber $node)
	{
		return null;
	}
	public function leaveMethodConstScalar(PhpParser_Node_Scalar_MethodConst $node)
	{
		return null;
	}
	public function leaveNSConstScalar(PhpParser_Node_Scalar_NSConst $node)
	{
		return null;
	}
	public function leaveStringScalar(PhpParser_Node_Scalar_String $node)
	{
		return null;
	}
	public function leaveTraitConstScalar(PhpParser_Node_Scalar_TraitConst $node)
	{
		return null;
	}
	public function leaveScalar(PhpParser_Node_Scalar $node)
	{
		return null;
	}
	public function leaveBreakStmt(PhpParser_Node_Stmt_Break $node)
	{
		return null;
	}
	public function leaveCaseStmt(PhpParser_Node_Stmt_Case $node)
	{
		return null;
	}
	public function leaveCatchStmt(PhpParser_Node_Stmt_Catch $node)
	{
		return null;
	}
	public function leaveClassStmt(PhpParser_Node_Stmt_Class $node)
	{
		return null;
	}
	public function leaveClassConstStmt(PhpParser_Node_Stmt_ClassConst $node)
	{
		return null;
	}
	public function leaveClassMethodStmt(PhpParser_Node_Stmt_ClassMethod $node)
	{
		return null;
	}
	public function leaveConstStmt(PhpParser_Node_Stmt_Const $node)
	{
		return null;
	}
	public function leaveContinueStmt(PhpParser_Node_Stmt_Continue $node)
	{
		return null;
	}
	public function leaveDeclareStmt(PhpParser_Node_Stmt_Declare $node)
	{
		return null;
	}
	public function leaveDeclareDeclareStmt(PhpParser_Node_Stmt_DeclareDeclare $node)
	{
		return null;
	}
	public function leaveDoStmt(PhpParser_Node_Stmt_Do $node)
	{
		return null;
	}
	public function leaveEchoStmt(PhpParser_Node_Stmt_Echo $node)
	{
		return null;
	}
	public function leaveElseStmt(PhpParser_Node_Stmt_Else $node)
	{
		return null;
	}
	public function leaveElseIfStmt(PhpParser_Node_Stmt_ElseIf $node)
	{
		return null;
	}
	public function leaveExprStmt(PhpParser_Node_Stmt_Expr $node)
	{
		return null;
	}
	public function leaveForStmt(PhpParser_Node_Stmt_For $node)
	{
		return null;
	}
	public function leaveForeachStmt(PhpParser_Node_Stmt_Foreach $node)
	{
		return null;
	}
	public function leaveFunctionStmt(PhpParser_Node_Stmt_Function $node)
	{
		return null;
	}
	public function leaveGlobalStmt(PhpParser_Node_Stmt_Global $node)
	{
		return null;
	}
	public function leaveGotoStmt(PhpParser_Node_Stmt_Goto $node)
	{
		return null;
	}
	public function leaveHaltCompilerStmt(PhpParser_Node_Stmt_HaltCompiler $node)
	{
		return null;
	}
	public function leaveIfStmt(PhpParser_Node_Stmt_If $node)
	{
		return null;
	}
	public function leaveInlineHTMLStmt(PhpParser_Node_Stmt_InlineHTML $node)
	{
		return null;
	}
	public function leaveInterfaceStmt(PhpParser_Node_Stmt_Interface $node)
	{
		return null;
	}
	public function leaveLabelStmt(PhpParser_Node_Stmt_Label $node)
	{
		return null;
	}
	public function leaveNamespaceStmt(PhpParser_Node_Stmt_Namespace $node)
	{
		return null;
	}
	public function leavePropertyStmt(PhpParser_Node_Stmt_Property $node)
	{
		return null;
	}
	public function leavePropertyPropertyStmt(PhpParser_Node_Stmt_PropertyProperty $node)
	{
		return null;
	}
	public function leaveReturnStmt(PhpParser_Node_Stmt_Return $node)
	{
		return null;
	}
	public function leaveStaticStmt(PhpParser_Node_Stmt_Static $node)
	{
		return null;
	}
	public function leaveStaticVarStmt(PhpParser_Node_Stmt_StaticVar $node)
	{
		return null;
	}
	public function leaveSwitchStmt(PhpParser_Node_Stmt_Switch $node)
	{
		return null;
	}
	public function leaveThrowStmt(PhpParser_Node_Stmt_Throw $node)
	{
		return null;
	}
	public function leaveTraitStmt(PhpParser_Node_Stmt_Trait $node)
	{
		return null;
	}
	public function leaveTraitUseStmt(PhpParser_Node_Stmt_TraitUse $node)
	{
		return null;
	}
	public function leaveAliasTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation_Alias $node)
	{
		return null;
	}
	public function leavePrecedenceTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation_Precedence $node)
	{
		return null;
	}
	public function leaveTraitUseAdaptationStmt(PhpParser_Node_Stmt_TraitUseAdaptation $node)
	{
		return null;
	}
	public function leaveTryCatchStmt(PhpParser_Node_Stmt_TryCatch $node)
	{
		return null;
	}
	public function leaveUnsetStmt(PhpParser_Node_Stmt_Unset $node)
	{
		return null;
	}
	public function leaveUseStmt(PhpParser_Node_Stmt_Use $node)
	{
		return null;
	}
	public function leaveUseUseStmt(PhpParser_Node_Stmt_UseUse $node)
	{
		return null;
	}
	public function leaveWhileStmt(PhpParser_Node_Stmt_While $node)
	{
		return null;
	}
	public function leaveStmt(PhpParser_Node_Stmt $node)
	{
		return null;
	}
	public function leaveExpr(PhpParser_Node_Expr $node)
	{
		return null;
	}

}
?>