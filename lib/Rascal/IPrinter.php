<?php

namespace Rascal;

interface IPrinter
{
	public function pprint(\PhpParser\Node $node);
	public function pprintArg(\PhpParser\Node\Arg $node);
	public function pprintConst(\PhpParser\Node\Const_ $node);
	public function pprintArrayExpr(\PhpParser\Node\Expr\Array_ $node);
	public function pprintArrayDimFetchExpr(\PhpParser\Node\Expr\ArrayDimFetch $node);
	public function pprintArrayItemExpr(\PhpParser\Node\Expr\ArrayItem $node);
	public function pprintAssignExpr(\PhpParser\Node\Expr\Assign $node);
	public function pprintAssignBitwiseAndExpr(\PhpParser\Node\Expr\AssignBitwiseAnd $node);
	public function pprintAssignBitwiseOrExpr(\PhpParser\Node\Expr\AssignBitwiseOr $node);
	public function pprintAssignBitwiseXorExpr(\PhpParser\Node\Expr\AssignBitwiseXor $node);
	public function pprintAssignConcatExpr(\PhpParser\Node\Expr\AssignConcat $node);
	public function pprintAssignDivExpr(\PhpParser\Node\Expr\AssignDiv $node);
	public function pprintAssignMinusExpr(\PhpParser\Node\Expr\AssignMinus $node);
	public function pprintAssignModExpr(\PhpParser\Node\Expr\AssignMod $node);
	public function pprintAssignMulExpr(\PhpParser\Node\Expr\AssignMul $node);
	public function pprintAssignPlusExpr(\PhpParser\Node\Expr\AssignPlus $node);
	public function pprintAssignRefExpr(\PhpParser\Node\Expr\AssignRef $node);
	public function pprintAssignShiftLeftExpr(\PhpParser\Node\Expr\AssignShiftLeft $node);
	public function pprintAssignShiftRightExpr(\PhpParser\Node\Expr\AssignShiftRight $node);
	public function pprintBitwiseAndExpr(\PhpParser\Node\Expr\BitwiseAnd $node);
	public function pprintBitwiseNotExpr(\PhpParser\Node\Expr\BitwiseNot $node);
	public function pprintBitwiseOrExpr(\PhpParser\Node\Expr\BitwiseOr $node);
	public function pprintBitwiseXorExpr(\PhpParser\Node\Expr\BitwiseXor $node);
	public function pprintBooleanAndExpr(\PhpParser\Node\Expr\BooleanAnd $node);
	public function pprintBooleanNotExpr(\PhpParser\Node\Expr\BooleanNot $node);
	public function pprintBooleanOrExpr(\PhpParser\Node\Expr\BooleanOr $node);
	public function pprintArrayCastExpr(\PhpParser\Node\Expr\Cast\Array_ $node);
	public function pprintBoolCastExpr(\PhpParser\Node\Expr\Cast\Bool $node);
	public function pprintDoubleCastExpr(\PhpParser\Node\Expr\Cast\Double $node);
	public function pprintIntCastExpr(\PhpParser\Node\Expr\Cast\Int $node);
	public function pprintObjectCastExpr(\PhpParser\Node\Expr\Cast\Object $node);
	public function pprintStringCastExpr(\PhpParser\Node\Expr\Cast\String $node);
	public function pprintUnsetCastExpr(\PhpParser\Node\Expr\Cast\Unset_ $node);
	public function pprintCastExpr(\PhpParser\Node\Expr\Cast $node);
	public function pprintClassConstFetchExpr(\PhpParser\Node\Expr\ClassConstFetch $node);
	public function pprintCloneExpr(\PhpParser\Node\Expr\Clone_ $node);
	public function pprintClosureExpr(\PhpParser\Node\Expr\Closure $node);
	public function pprintClosureUseExpr(\PhpParser\Node\Expr\ClosureUse $node);
	public function pprintConcatExpr(\PhpParser\Node\Expr\Concat $node);
	public function pprintConstFetchExpr(\PhpParser\Node\Expr\ConstFetch $node);
	public function pprintDivExpr(\PhpParser\Node\Expr\Div $node);
	public function pprintEmptyExpr(\PhpParser\Node\Expr\Empty_ $node);
	public function pprintEqualExpr(\PhpParser\Node\Expr\Equal $node);
	public function pprintErrorSuppressExpr(\PhpParser\Node\Expr\ErrorSuppress $node);
	public function pprintEvalExpr(\PhpParser\Node\Expr\Eval_ $node);
	public function pprintExitExpr(\PhpParser\Node\Expr\Exit_ $node);
	public function pprintFuncCallExpr(\PhpParser\Node\Expr\FuncCall $node);
	public function pprintGreaterExpr(\PhpParser\Node\Expr\Greater $node);
	public function pprintGreaterOrEqualExpr(\PhpParser\Node\Expr\GreaterOrEqual $node);
	public function pprintIdenticalExpr(\PhpParser\Node\Expr\Identical $node);
	public function pprintIncludeExpr(\PhpParser\Node\Expr\Include_ $node);
	public function pprintInstanceofExpr(\PhpParser\Node\Expr\Instanceof_ $node);
	public function pprintIssetExpr(\PhpParser\Node\Expr\Isset_ $node);
	public function pprintListExpr(\PhpParser\Node\Expr\List_ $node);
	public function pprintLogicalAndExpr(\PhpParser\Node\Expr\LogicalAnd $node);
	public function pprintLogicalOrExpr(\PhpParser\Node\Expr\LogicalOr $node);
	public function pprintLogicalXorExpr(\PhpParser\Node\Expr\LogicalXor $node);
	public function pprintMethodCallExpr(\PhpParser\Node\Expr\MethodCall $node);
	public function pprintMinusExpr(\PhpParser\Node\Expr\Minus $node);
	public function pprintModExpr(\PhpParser\Node\Expr\Mod $node);
	public function pprintMulExpr(\PhpParser\Node\Expr\Mul $node);
	public function pprintNewExpr(\PhpParser\Node\Expr\New_ $node);
	public function pprintNotEqualExpr(\PhpParser\Node\Expr\NotEqual $node);
	public function pprintNotIdenticalExpr(\PhpParser\Node\Expr\NotIdentical $node);
	public function pprintPlusExpr(\PhpParser\Node\Expr\Plus $node);
	public function pprintPostDecExpr(\PhpParser\Node\Expr\PostDec $node);
	public function pprintPostIncExpr(\PhpParser\Node\Expr\PostInc $node);
	public function pprintPreDecExpr(\PhpParser\Node\Expr\PreDec $node);
	public function pprintPreIncExpr(\PhpParser\Node\Expr\PreInc $node);
	public function pprintPrintExpr(\PhpParser\Node\Expr\Print_ $node);
	public function pprintPropertyFetchExpr(\PhpParser\Node\Expr\PropertyFetch $node);
	public function pprintShellExecExpr(\PhpParser\Node\Expr\ShellExec $node);
	public function pprintShiftLeftExpr(\PhpParser\Node\Expr\ShiftLeft $node);
	public function pprintShiftRightExpr(\PhpParser\Node\Expr\ShiftRight $node);
	public function pprintSmallerExpr(\PhpParser\Node\Expr\Smaller $node);
	public function pprintSmallerOrEqualExpr(\PhpParser\Node\Expr\SmallerOrEqual $node);
	public function pprintStaticCallExpr(\PhpParser\Node\Expr\StaticCall $node);
	public function pprintStaticPropertyFetchExpr(\PhpParser\Node\Expr\StaticPropertyFetch $node);
	public function pprintTernaryExpr(\PhpParser\Node\Expr\Ternary $node);
	public function pprintUnaryMinusExpr(\PhpParser\Node\Expr\UnaryMinus $node);
	public function pprintUnaryPlusExpr(\PhpParser\Node\Expr\UnaryPlus $node);
	public function pprintVariableExpr(\PhpParser\Node\Expr\Variable $node);
	public function pprintYieldExpr(\PhpParser\Node\Expr\Yield_ $node);
	public function pprintFullyQualifiedName(\PhpParser\Node\Name\FullyQualified $node);
	public function pprintRelativeName(\PhpParser\Node\Name\Relative $node);
	public function pprintName(\PhpParser\Node\Name $node);
	public function pprintParam(\PhpParser\Node\Param $node);
	public function pprintClassConstScalar(\PhpParser\Node\Scalar\ClassConst $node);
	public function pprintDirConstScalar(\PhpParser\Node\Scalar\DirConst $node);
	public function pprintDNumberScalar(\PhpParser\Node\Scalar\DNumber $node);
	public function pprintEncapsedScalar(\PhpParser\Node\Scalar\Encapsed $node);
	public function pprintFileConstScalar(\PhpParser\Node\Scalar\FileConst $node);
	public function pprintFuncConstScalar(\PhpParser\Node\Scalar\FuncConst $node);
	public function pprintLineConstScalar(\PhpParser\Node\Scalar\LineConst $node);
	public function pprintLNumberScalar(\PhpParser\Node\Scalar\LNumber $node);
	public function pprintMethodConstScalar(\PhpParser\Node\Scalar\MethodConst $node);
	public function pprintNSConstScalar(\PhpParser\Node\Scalar\NSConst $node);
	public function pprintStringScalar(\PhpParser\Node\Scalar\String $node);
	public function pprintTraitConstScalar(\PhpParser\Node\Scalar\TraitConst $node);
	public function pprintScalar(\PhpParser\Node\Scalar $node);
	public function pprintBreakStmt(\PhpParser\Node\Stmt\Break_ $node);
	public function pprintCaseStmt(\PhpParser\Node\Stmt\Case_ $node);
	public function pprintCatchStmt(\PhpParser\Node\Stmt\Catch_ $node);
	public function pprintClassStmt(\PhpParser\Node\Stmt\Class_ $node);
	public function pprintClassConstStmt(\PhpParser\Node\Stmt\ClassConst $node);
	public function pprintClassMethodStmt(\PhpParser\Node\Stmt\ClassMethod $node);
	public function pprintConstStmt(\PhpParser\Node\Stmt\Const_ $node);
	public function pprintContinueStmt(\PhpParser\Node\Stmt\Continue_ $node);
	public function pprintDeclareStmt(\PhpParser\Node\Stmt\Declare_ $node);
	public function pprintDeclareDeclareStmt(\PhpParser\Node\Stmt\DeclareDeclare $node);
	public function pprintDoStmt(\PhpParser\Node\Stmt\Do_ $node);
	public function pprintEchoStmt(\PhpParser\Node\Stmt\Echo_ $node);
	public function pprintElseStmt(\PhpParser\Node\Stmt\Else_ $node);
	public function pprintElseIfStmt(\PhpParser\Node\Stmt\ElseIf_ $node);
	public function pprintExprStmt(\PhpParser\Node\Stmt\Expr $node);
	public function pprintForStmt(\PhpParser\Node\Stmt\For_ $node);
	public function pprintForeachStmt(\PhpParser\Node\Stmt\Foreach_ $node);
	public function pprintFunctionStmt(\PhpParser\Node\Stmt\Function_ $node);
	public function pprintGlobalStmt(\PhpParser\Node\Stmt\Global_ $node);
	public function pprintGotoStmt(\PhpParser\Node\Stmt\Goto_ $node);
	public function pprintHaltCompilerStmt(\PhpParser\Node\Stmt\HaltCompiler $node);
	public function pprintIfStmt(\PhpParser\Node\Stmt\If_ $node);
	public function pprintInlineHTMLStmt(\PhpParser\Node\Stmt\InlineHTML $node);
	public function pprintInterfaceStmt(\PhpParser\Node\Stmt\Interface_ $node);
	public function pprintLabelStmt(\PhpParser\Node\Stmt\Label $node);
	public function pprintNamespaceStmt(\PhpParser\Node\Stmt\Namespace_ $node);
	public function pprintPropertyStmt(\PhpParser\Node\Stmt\Property $node);
	public function pprintPropertyPropertyStmt(\PhpParser\Node\Stmt\PropertyProperty $node);
	public function pprintReturnStmt(\PhpParser\Node\Stmt\Return_ $node);
	public function pprintStaticStmt(\PhpParser\Node\Stmt\Static_ $node);
	public function pprintStaticVarStmt(\PhpParser\Node\Stmt\StaticVar $node);
	public function pprintSwitchStmt(\PhpParser\Node\Stmt\Switch_ $node);
	public function pprintThrowStmt(\PhpParser\Node\Stmt\Throw_ $node);
	public function pprintTraitStmt(\PhpParser\Node\Stmt\Trait_ $node);
	public function pprintTraitUseStmt(\PhpParser\Node\Stmt\TraitUse $node);
	public function pprintAliasTraitUseAdaptationStmt(\PhpParser\Node\Stmt\TraitUseAdaptation\Alias $node);
	public function pprintPrecedenceTraitUseAdaptationStmt(\PhpParser\Node\Stmt\TraitUseAdaptation\Precedence $node);
	public function pprintTraitUseAdaptationStmt(\PhpParser\Node\Stmt\TraitUseAdaptation $node);
	public function pprintTryCatchStmt(\PhpParser\Node\Stmt\TryCatch $node);
	public function pprintUnsetStmt(\PhpParser\Node\Stmt\Unset_ $node);
	public function pprintUseStmt(\PhpParser\Node\Stmt\Use_ $node);
	public function pprintUseUseStmt(\PhpParser\Node\Stmt\UseUse $node);
	public function pprintWhileStmt(\PhpParser\Node\Stmt\While_ $node);
	public function pprintStmt(\PhpParser\Node\Stmt $node);
	public function pprintExpr(\PhpParser\Node\Expr $node);

}
?>