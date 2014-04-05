<?php

namespace Rascal;


use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rascal\NodeVisitor\NameResolver as NameResolverRascal;

class DeclarationsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PhpParser\Parser $parser
     */
    private $parser;

    /**
     * @var \Rascal\RascalPrinter
     */
    private $printer;

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
    }

    /**
     * @dataProvider getTestCode
     */
    public function testClassDeclarations($code, $expectedDeclarations)
    {
        try {
            $this->parseAndValidateResults($code, $expectedDeclarations);
        } catch (\PhpParser\Error $e) {
            $this->fail("Error in parsing: " . $e->getMessage());
        } catch (\Exception $e) {
            $this->fail("General error: " . $e->getMessage());
        }
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
                '<?php namespace ns1 {} namespace ns2 {}',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+namespace:///ns2|',
                )
            ),
            array(
                '<?php namespace ns1 {} namespace ns2 {} namespace {}',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+namespace:///ns2|',
                    '@decl=|php+namespace:///-|',
                )
            ),

            // class tests (and namespace)
            array(
                'code' => '<?php namespace ns1 { class cl1 {} } namespace ns1\subNs { class cl2 {} } namespace ns1\subNs\subSubNs { class cl3 {} }',
                array(
                    '@decl=|php+class:///ns1/cl1|',
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+class:///ns1\\\\subNs/cl2|',
                    '@decl=|php+namespace:///ns1\\\\subNs|',
                    '@decl=|php+class:///ns1\\\\subNs\\\\subSubNs/cl3|',
                    '@decl=|php+namespace:///ns1\\\\subNs\\\\subSubNs|',
                )
            ),
            array(
                'code' => '<?php namespace ns1; use Main\Sub\Class2 as Cl2; class Class1 { public function __construct() { $cl2 = new Cl2; } }',
                array(
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+class:///ns1/Class1|',
                    '@decl=|php+method:///ns1/Class1/__construct|',
                    '@decl=|php+variable:///ns1/Class1/__construct/-/cl2|',
                )
            ),

            // interface test
            array(
                'code' => '<?php namespace ns1 { interface if1 {} } namespace ns2 { interface if1 {} }',
                array(
                    '@decl=|php+interface:///ns1/if1|',
                    '@decl=|php+namespace:///ns1|',
                    '@decl=|php+interface:///ns2/if1|',
                    '@decl=|php+namespace:///ns2|',
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
                    '@decl=|php+method:///-/cl1/m1|',
                    '@decl=|php+method:///-/cl1/m2|',
                    '@decl=|php+method:///-/cl1/m3|',
                    '@decl=|php+class:///-/cl1|',
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
                'code' => '<?php class cl1 { public $m1; protected $m2; private $m3; }',
                array(
                    '@decl=|php+field:///-/cl1/m1|',
                    '@decl=|php+field:///-/cl1/m2|',
                    '@decl=|php+field:///-/cl1/m3|',
                    '@decl=|php+class:///-/cl1|',
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
                    '@decl=|php+variable:///-/-/-/-/one|',
                    '@decl=|php+class:///-/cl1|',
                    '@decl=|php+variable:///-/-/-/-/two|',
                )
            ),
            array(
                'code' => '<?php $one=1; class cl1 { public function x() { $three=3; } }',
                array(
                    '@decl=|php+variable:///-/-/-/-/one|',
                    '@decl=|php+variable:///-/cl1/x/-/three|',
                    '@decl=|php+method:///-/cl1/x|',
                    '@decl=|php+class:///-/cl1|',
                )
            ),
            array(
                'code' => '<?php $one=1; class cl1 { public function x() { $two=2; function y() { $three=3; } } }',
                array(
                    '@decl=|php+variable:///-/-/-/-/one|',
                    '@decl=|php+variable:///-/cl1/x/-/two|',
                    '@decl=|php+variable:///-/cl1/x/y/three|',
                    '@decl=|php+function:///-/cl1/x/y|',
                    '@decl=|php+method:///-/cl1/x|',
                    '@decl=|php+class:///-/cl1|',
                )
            ),
            array(
                'code' => '<?php $one=1; interface cl1 { public function x() { $two=2; function y() { $three=3; } } }',
                array(
                    '@decl=|php+variable:///-/-/-/-/one|',
                    '@decl=|php+variable:///-/cl1/x/-/two|',
                    '@decl=|php+variable:///-/cl1/x/y/three|',
                    '@decl=|php+function:///-/cl1/x/y|',
                    '@decl=|php+method:///-/cl1/x|',
                    '@decl=|php+interface:///-/cl1|',
                )
            ),
            array(
                'code' => '<?php $one=1; trait cl1 { public function x() { $two=2; function y() { $three=3; } } }',
                array(
                    '@decl=|php+variable:///-/-/-/-/one|',
                    '@decl=|php+variable:///-/cl1/x/-/two|',
                    '@decl=|php+variable:///-/cl1/x/y/three|',
                    '@decl=|php+function:///-/cl1/x/y|',
                    '@decl=|php+method:///-/cl1/x|',
                    '@decl=|php+trait:///-/cl1|',
                )
            ),

            // method and function parameters
            array(
                'code' => '<?php $one=1; function two ($three) { $four=4; } $five=5;',
                array(
                    '@decl=|php+variable:///-/-/-/-/one|',
                    '@decl=|php+parameter:///-/-/-/two/three|',
                    '@decl=|php+variable:///-/-/-/two/four|',
                    '@decl=|php+function:///-/-/-/two|',
                    '@decl=|php+variable:///-/-/-/-/five|',
                )
            ),
            array(
                'code' => '<?php $one=1; class two { public function three ($four) { function five ($six) { $seven=7; } $eight=8; } } $nine=9;',
                array(
                    '@decl=|php+variable:///-/-/-/-/one|',
                    '@decl=|php+parameter:///-/two/three/-/four|',
                    '@decl=|php+parameter:///-/two/three/five/six|',
                    '@decl=|php+variable:///-/two/three/five/seven|',
                    '@decl=|php+function:///-/two/three/five|',
                    '@decl=|php+variable:///-/two/three/-/eight|',
                    '@decl=|php+method:///-/two/three|',
                    '@decl=|php+class:///-/two|',
                    '@decl=|php+variable:///-/-/-/-/nine|',
                )
            ),
            array(
                'code' => '<?php function f ($a, $b, array $c) {}',
                array(
                    '@decl=|php+parameter:///-/-/-/f/a|',
                    '@decl=|php+parameter:///-/-/-/f/b|',
                    '@decl=|php+parameter:///-/-/-/f/c|',
                    '@decl=|php+function:///-/-/-/f|',
                )
            ),

            // variable declarations
            array(
                'code' => '<?php $a = 1; $b = $a;',
                array(
                    '@decl=|php+variable:///-/-/-/-/a|',
                    '@decl=|php+variable:///-/-/-/-/b|',
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
                "PhpParser\Node\Expr\AssignOp\Mod"          \=
                "PhpParser\Node\Expr\AssignOp\Mul"          *=
                "PhpParser\Node\Expr\AssignOp\Plus"         +=
                "PhpParser\Node\Expr\AssignOp\ShiftLeft"    <<=
                "PhpParser\Node\Expr\AssignOp\ShiftRight"   >>=
             */
            array(
                'code' => '<?php $a &= 1; $b |= 1; $c ^= 1; $d .= 1; $e /= 1; $f -= 1; $g %= 1; $h *= 1; $i += 1; $j <<= 1; $k >>= 1;',
                array(
                    '@decl=|php+variable:///-/-/-/-/a|',
                    '@decl=|php+variable:///-/-/-/-/b|',
                    '@decl=|php+variable:///-/-/-/-/c|',
                    '@decl=|php+variable:///-/-/-/-/d|',
                    '@decl=|php+variable:///-/-/-/-/e|',
                    '@decl=|php+variable:///-/-/-/-/f|',
                    '@decl=|php+variable:///-/-/-/-/g|',
                    '@decl=|php+variable:///-/-/-/-/h|',
                    '@decl=|php+variable:///-/-/-/-/i|',
                    '@decl=|php+variable:///-/-/-/-/j|',
                    '@decl=|php+variable:///-/-/-/-/k|',
                )
            ),

            // variable variables
            // TODO: variable variables are not properly handled in RascalPrinter
            array(
                'code' => '<?php $c="CEE"; $a = "c"; $a = $$a;',
                array(
                    '@decl=|php+variable:///-/-/-/-/c|',
                    '@decl=|php+variable:///-/-/-/-/a|',
                    '@decl=|php+variable:///-/-/-/-/a|',
                )
            ),
            array(
                'code' => '<?php $$a = 1;',
                array(
                    '@decl=|php+variable:///-/-/-/-/a|',
                )
            ),
            array(
                'code' => '<?php $$$a = 1;',
                array(
                    '@decl=|php+variable:///-/-/-/-/a|',
                )
            ),

            // variables in eval
            array(
                'code' => '<?php  eval("\$a = \'a\';"); $b = $a;',
                array(
                    //'@decl=|php+variable:///////a|', GETTING DECLARATIONS FROM EVAL DOES NOT WORK!!!
                    '@decl=|php+variable:///-/-/-/-/b|',
                )
            ),
        );
    }

    /**
     * @param string $code
     * @param array $expectedDeclarations
     */
    private function parseAndValidateResults($code, array $expectedDeclarations)
    {
        $stmtStr = $this->codeToRascalAST($code);

        if (preg_match_all('/(@decl=\|([^|]*)?\|)/', $stmtStr, $matches)) {
            $declarations = $matches[1];
            $this->assertEquals(count($declarations), count($expectedDeclarations));

            foreach ($declarations as $declaration) {
                $this->assertContains($declaration, $expectedDeclarations);
            }
            foreach ($expectedDeclarations as $declaration) {
                $this->assertContains($declaration, $declarations);
            }

        } else {
            $this->fail("No declarations found in code: " . $code);
        }
    }

    /**
     * @param string $code
     * @return string
     */
    private function codeToRascalAST($code)
    {
        $parseTree = $this->parser->parse($code);

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);
        $traverser->addVisitor(new NameResolverRascal);
        $traverser->traverse($parseTree);

        $stmtStr = '';
        foreach ($parseTree as $node) {
            $stmtStr .= $this->printer->pprint($node);
        }

        return $stmtStr;
    }
}
 