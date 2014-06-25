<?php

namespace Rascal;

class PhpDocTest extends \PHPUnit_Framework_TestCase
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
            $addPhpDocs = true,
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
            // class tests
            array(
                'code' => '<?php
                    /** classAnno */
                    class cl1 {} ',
                array(
                    '@phpdoc="/** classAnno */"',
                    '@phpdoc="/** classAnno */"', // double entry for classes, added on ClassDef and class()
                )
            ),

            // method test
            array(
                'code' => '<?php
                    class cl1
                    {
                        /** @param string $abc */
                        public function __construct($abc) {}
                        /** @param string $static */
                        static public function factory($static) {}
                    } ',
                array(
                    '@phpdoc="/** @param string $abc */"',
                    '@phpdoc="/** @param string $static */"',
                )
            ),

            // field test
            array(
                'code' => '<?php
                    class cl1
                    {
                        /** @var Document */
                        public $document = null;
                        /** @var Library */
                        public $library;
                    } ',
                array(
                    '@phpdoc="/** @var Document */"',
                    '@phpdoc="/** @var Library */"',
                )
            ),

            // function test
            array(
                'code' => '<?php
                    /**
                     * @param string $a
                     * @return Object
                     */
                    function getObject($a) { return new $a; }',
                array(
                    '@phpdoc="/**n * @param string $an * @return Objectn */"', // slashes and double spaces are removed
                )
            ),

            // var test
            array(
                'code' => '<?php
                    /** @var Cl $a */
                    $a = $b->getObj();',
                array(
                    '@phpdoc="/** @var Cl $a */"',
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
        $stmtStr = stripslashes($stmtStr);
        $stmtStr = preg_replace("/\s+/", " ", $stmtStr); // remove double spaces

        if (preg_match_all('/(@phpdoc="([^"]*)?")/', $stmtStr, $matches)) {
            $declarations = $matches[1];
            $this->assertEquals(count($declarations), count($expectedDeclarations));

            foreach ($declarations as $declaration) {
                $this->assertContains($declaration, $expectedDeclarations);
            }
            foreach ($expectedDeclarations as $declaration) {
                $this->assertContains($declaration, $declarations);
            }

        } else {
            $this->fail("No phpdoc found in code: " . $code);
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
}
 