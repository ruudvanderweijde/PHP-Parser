<?php

namespace Rascal;

abstract class AnnotationTestAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PhpParser\Parser $parser
     */
    protected $parser;

    /**
     * @var \Rascal\RascalPrinter
     */
    protected $printer;

    /**
     * @var string
     */
    private $regex;

    /**
     * @dataProvider getTestCode
     */
    public function testAnnotations($code, $expectedDeclarations)
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
    abstract public function getTestCode();

    /**
     * @param string $code
     * @param array $expectedDeclarations
     */
    private function parseAndValidateResults($code, array $expectedDeclarations)
    {
        $stmtStr = $this->codeToRascalAST($code);
        $stmtStr = stripslashes($stmtStr);

        if (preg_match_all($this->regex, $stmtStr, $matches)) {
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

        $stmtStr = '';
        foreach ($parseTree as $node) {
            $stmtStr .= $this->printer->pprint($node);
        }

        return $stmtStr;
    }

    /**
     * @param string $regex
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
    }
}
