<?php

namespace Rascal;

require_once __DIR__ . '/AnnotationTestAbstract.php';

class DeclarationsTest extends AnnotationTestAbstract
{
    public function setUp()
    {
        $this->parser = new \PhpParser\Parser(new \PhpParser\Lexer);
        $this->printer = new RascalPrinter(
            $fileName = "",
            $addLocations = false,
            $relativeLocations = false,
            $addIds = false,
            $idPrefix = false,
            $addPhpDocs = false,
            $addDeclarations = true
        );
        $this->setRegex('/(@decl=\|([^|]*)?\|)/');
    }

    /**
     * Test data: array(code, expectedDeclarations);
     *
     * @return array
     */
    public function getTestCode()
    {
        return array(
            // namespace tests
            array(
                '<?php namespace ns1 {} namespace ns2\ns3 {} namespace {}',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+namespace:///ns2/ns3|',
                    '@decl=|php+namespace:///|',
                )
            ),
            array(
                '<?php namespace ns1; $one=1; namespace ns2\ns3; $two=2;',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+namespace:///ns2/ns3|',
                    '@decl=|php+globalVar:///ns1/one|',
                    '@decl=|php+globalVar:///ns2/ns3/two|',
                )
            ),
            array(
                '<?php namespace ns1; use \ns2\ns3\c3 as c30; $c30 = new c30; namespace ns2\ns3; trait t3 {} class c3 {}',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+namespace:///ns2/ns3|',
                    '@decl=|php+globalVar:///ns1/c30|',
                    '@decl=|php+trait:///ns2/ns3/t3|',
                    '@decl=|php+class:///ns2/ns3/c3|',
                )
            ),

            // class tests (and namespace)
            array(
                'code' => '<?php namespace ns1 { class cl1 {} } namespace ns1\subNs { class cl2 {} } namespace ns1\subNs\subSubNs { class cl3 {} }',
                array(
                    '@decl=|php+class:///ns1/cl1|',
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+class:///ns1/subns/cl2|',
                    '@decl=|php+namespace:///ns1/subns|',
                    '@decl=|php+class:///ns1/subns/subsubns/cl3|',
                    '@decl=|php+namespace:///ns1/subns/subsubns|',
                )
            ),
            array( // same as the test above, except for curly braces around namespaces
                'code' => '<?php namespace ns1; class cl1 {} namespace ns1\subNs; class cl2 {} namespace ns1\subNs\subSubNs; class cl3 {}',
                array(
                    '@decl=|php+class:///ns1/cl1|',
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+class:///ns1/subns/cl2|',
                    '@decl=|php+namespace:///ns1/subns|',
                    '@decl=|php+class:///ns1/subns/subsubns/cl3|',
                    '@decl=|php+namespace:///ns1/subns/subsubns|',
                )
            ),
            array(
                'code' => '<?php namespace ns1; use Main\Sub\Class2 as Cl2; class Class1 { public function __construct() { $cl2 = new Cl2; } }',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+class:///ns1/class1|',
                    '@decl=|php+method:///ns1/class1/__construct|',
                    '@decl=|php+methodVar:///ns1/class1/__construct/cl2|',
                )
            ),

            // interface test
            array(
                'code' => '<?php interface if1 { const c=1; public function f($id);}',
                array(
                    '@decl=|php+interface:///if1|',
                    '@decl=|php+classConstant:///if1/c|',
                    '@decl=|php+method:///if1/f|',
                    '@decl=|php+methodParam:///if1/f/id|',
                )
            ),
            array(
                'code' => '<?php namespace ns1 { interface if1 {} } namespace ns2 { interface if1 { const c=1; public function f($id);} }',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+interface:///ns1/if1|',
                    '@decl=|php+namespace:///ns2|',
                    '@decl=|php+interface:///ns2/if1|',
                    '@decl=|php+classConstant:///ns2/if1/c|',
                    '@decl=|php+method:///ns2/if1/f|',
                    '@decl=|php+methodParam:///ns2/if1/f/id|',
                )
            ),

            // trait test
            array(
                'code' => '<?php namespace ns1 { trait tr1 {} } namespace ns2 { trait tr1 {} }',
                array(
                    '@decl=|php+trait:///ns1/tr1|',
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+trait:///ns2/tr1|',
                    '@decl=|php+namespace:///ns2|',
                )
            ),

            // class method tests
            array(
                'code' => '<?php class cl1 { public function m1() {} protected function m2() {} private function m3() {} }',
                array(
                    '@decl=|php+method:///cl1/m1|',
                    '@decl=|php+method:///cl1/m2|',
                    '@decl=|php+method:///cl1/m3|',
                    '@decl=|php+class:///cl1|',
                )
            ),
            array( // inside namespace
                'code' => '<?php namespace ns1 { class cl1 { public function m1() {} protected function m2() {} private function m3() {} } }',
                array(
                    '@decl=|php+method:///ns1/cl1/m1|',
                    '@decl=|php+method:///ns1/cl1/m2|',
                    '@decl=|php+method:///ns1/cl1/m3|',
                    '@decl=|php+class:///ns1/cl1|',
                    '@decl=|php+namespace:///ns1|',
                )
            ),

            // class field tests
            array(
                'code' => '<?php class cl1 { public $m1; protected $m2; private $m3; static $m4; }',
                array(
                    '@decl=|php+field:///cl1/m1|',
                    '@decl=|php+field:///cl1/m2|',
                    '@decl=|php+field:///cl1/m3|',
                    '@decl=|php+field:///cl1/m4|',
                    '@decl=|php+class:///cl1|',
                )
            ),
            array( // inside namespace
                'code' => '<?php namespace ns1 { class cl1 { public $m1; protected $m2; private $m3; } }',
                array(
                    '@decl=|php+field:///ns1/cl1/m1|',
                    '@decl=|php+field:///ns1/cl1/m2|',
                    '@decl=|php+field:///ns1/cl1/m3|',
                    '@decl=|php+class:///ns1/cl1|',
                    '@decl=|php+namespace:///ns1|',
                )
            ),

            // var and function tests
            array(
                'code' => '<?php $one=1; class cl1 { } $two=2;',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+class:///cl1|',
                    '@decl=|php+globalVar:///two|',
                )
            ),
            array(
                'code' => '<?php $one=1; class cl1 { public function x() { $three=3; } }',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+methodVar:///cl1/x/three|',
                    '@decl=|php+method:///cl1/x|',
                    '@decl=|php+class:///cl1|',
                )
            ),
            array(
                'code' => '<?php $one=1; class cl1 { public function x() { $two=2; function y() { $three=3; } } }',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+methodVar:///cl1/x/two|',
                    '@decl=|php+functionVar:///y/three|',
                    '@decl=|php+function:///y|',
                    '@decl=|php+method:///cl1/x|',
                    '@decl=|php+class:///cl1|',
                )
            ),
            array(
                'code' => '<?php $one=1; interface cl1 { public function x() { $two=2; function y() { $three=3; } } }',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+methodVar:///cl1/x/two|',
                    '@decl=|php+functionVar:///y/three|',
                    '@decl=|php+function:///y|',
                    '@decl=|php+method:///cl1/x|',
                    '@decl=|php+interface:///cl1|',
                )
            ),
            array(
                'code' => '<?php $one=1; trait cl1 { public function x() { $two=2; function y() { $three=3; } } }',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+methodVar:///cl1/x/two|',
                    '@decl=|php+functionVar:///y/three|',
                    '@decl=|php+function:///y|',
                    '@decl=|php+method:///cl1/x|',
                    '@decl=|php+trait:///cl1|',
                )
            ),
            array(
                'code' => '<?php function One() { $_1=1; function Two() { $_2=2; class Three { public $_3=3; public function Four() { $_4=4; function Five() {$_5=5;} } } } }',
                array(
                    '@decl=|php+function:///one|',
                    '@decl=|php+function:///two|',
                    '@decl=|php+class:///three|',
                    '@decl=|php+method:///three/four|',
                    '@decl=|php+function:///five|',
                    '@decl=|php+functionVar:///one/_1|',
                    '@decl=|php+functionVar:///two/_2|',
                    '@decl=|php+field:///three/_3|',
                    '@decl=|php+methodVar:///three/four/_4|',
                    '@decl=|php+functionVar:///five/_5|',
                )
            ),

            // method and function parameters
            array(
                'code' => '<?php $one=1; function two ($three) { $four=4; } $five=5;',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+functionParam:///two/three|',
                    '@decl=|php+functionVar:///two/four|',
                    '@decl=|php+function:///two|',
                    '@decl=|php+globalVar:///five|',
                )
            ),
            array(
                'code' => '<?php $one=1; class two { public function three ($four) { function five ($six) { $seven=7; } $eight=8; } } $nine=9;',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+class:///two|',
                    '@decl=|php+method:///two/three|',
                    '@decl=|php+methodParam:///two/three/four|',
                    '@decl=|php+function:///five|',
                    '@decl=|php+functionParam:///five/six|',
                    '@decl=|php+functionVar:///five/seven|',
                    '@decl=|php+methodVar:///two/three/eight|',
                    '@decl=|php+globalVar:///nine|',
                )
            ),
            // anonymous function
            array(
                'code' => '<?php $greet = function($n) { echo $n; };',
                array(
                    '@decl=|php+globalVar:///greet|',
                    // closure are not part of the declarations (yet)
                )
            ),
            array(
                'code' => '<?php function f ($a=true, $b, array $c) {}',
                array(
                    '@decl=|php+functionParam:///f/a|',
                    '@decl=|php+functionParam:///f/b|',
                    '@decl=|php+functionParam:///f/c|',
                    '@decl=|php+function:///f|',
                )
            ),

            // variable declarations
            array(
                'code' => '<?php $a = 1; $b = $a; function f ($p) { $v=4; }',
                array(
                    '@decl=|php+globalVar:///a|',
                    '@decl=|php+globalVar:///b|',
                    '@decl=|php+function:///f|',
                    '@decl=|php+functionParam:///f/p|',
                    '@decl=|php+functionVar:///f/v|',
                )
            ),
            /**
             * test :
                "PhpParser\Node\Expr\AssignOp\BitwiseAnd"   &=
                "PhpParser\Node\Expr\AssignOp\BitwiseOr"    |=
                "PhpParser\Node\Expr\AssignOp\BitwiseXor"   ^=
                "PhpParser\Node\Expr\AssignOp\Concat"       .=
                "PhpParser\Node\Expr\AssignOp\Div"          /=
                "PhpParser\Node\Expr\AssignOp\Minus"        -=
                "PhpParser\Node\Expr\AssignOp\Mod"          %=
                "PhpParser\Node\Expr\AssignOp\Mul"          *=
                "PhpParser\Node\Expr\AssignOp\Plus"         +=
                "PhpParser\Node\Expr\AssignOp\ShiftLeft"    <<=
                "PhpParser\Node\Expr\AssignOp\ShiftRight"   >>=
             */
            array(
                'code' => '<?php $a &= 1; $b |= 1; $c ^= 1; $d .= 1; $e /= 1; $f -= 1; $g %= 1; $h *= 1; $i += 1; $j <<= 1; $k >>= 1;',
                array(
                    '@decl=|php+globalVar:///a|',
                    '@decl=|php+globalVar:///b|',
                    '@decl=|php+globalVar:///c|',
                    '@decl=|php+globalVar:///d|',
                    '@decl=|php+globalVar:///e|',
                    '@decl=|php+globalVar:///f|',
                    '@decl=|php+globalVar:///g|',
                    '@decl=|php+globalVar:///h|',
                    '@decl=|php+globalVar:///i|',
                    '@decl=|php+globalVar:///j|',
                    '@decl=|php+globalVar:///k|',
                )
            ),
            array(
                'code' => '<?php $a++; ++$b; $c--; --$d;',
                array(
                    '@decl=|php+globalVar:///a|',
                    '@decl=|php+globalVar:///b|',
                    '@decl=|php+globalVar:///c|',
                    '@decl=|php+globalVar:///d|',
                )
            ),

            // conditional declarations
            array(
                'code' => '<?php if (true) { $one=1; } if (1) { function two ($three) { $four=4; } } if (1) { class X {} } else { class X {} } ',
                array(
                    '@decl=|php+globalVar:///one|',
                    '@decl=|php+functionParam:///two/three|',
                    '@decl=|php+functionVar:///two/four|',
                    '@decl=|php+function:///two|',
                    '@decl=|php+class:///x|',
                    '@decl=|php+class:///x|',
                )
            ),

            // constants
            array(
                'code' => '<?php namespace ns1; const a=1;',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+constant:///ns1/a|',
                )
            ),
            array(
                'code' => '<?php const a=1;',
                array(
                    '@decl=|php+constant:///a|',
                )
            ),

            // static vars
            array(
                'code' => '<?php static $a; function b() { static $c = 1; }',
                array(
                    '@decl=|php+globalVar:///a|',
                    '@decl=|php+function:///b|',
                    '@decl=|php+functionVar:///b/c|',

                ),
            ),
            // globalVar variables
            array(
                'code' => '<?php $c="CEE"; $a = "c"; $a = $$a;',
                array(
                    '@decl=|php+globalVar:///c|',
                    '@decl=|php+globalVar:///a|',
                    '@decl=|php+globalVar:///a|',
                )
            ),
            array(
                'code' => '<?php $$$a = 1; $b++;',
                array(
                    '@decl=|php+unresolved+globalVar:///|',
                    '@decl=|php+unresolved+globalVar:///|', // extra annotation added: on var and expr
                    '@decl=|php+globalVar:///b|',
                )
            ),
            array(
                'code' => '<?php $$$a = 1;',
                array(
                    '@decl=|php+unresolved+globalVar:///|', // this is not right
                    '@decl=|php+unresolved+globalVar:///|', // extra annotation added: on var and expr
                )
            ),

            // variables in eval
            array(
                'code' => '<?php  eval("\$a = \'a\';"); $b = $a;',
                array(
                    //'@decl=|php+globalVar:///a|', GETTING DECLARATIONS FROM EVAL DOES NOT WORK!!!
                    '@decl=|php+globalVar:///b|',
                )
            ),
        );
    }
}
