<?php

namespace Rascal;

use PhpParser\Parser;
use PhpParser\Lexer;

require_once __DIR__ . '/CodeTestAbstract.php';

class AST2RascalTest extends CodeTestAbstract
{
    protected $parser;

    public function setUp()
    {
        $this->parser = new Parser(new Lexer());
    }
    /**
     * @dataProvider provideRascalFiles
     */
    public function testParseSuccess($name, $code, $expected) {
        $actual = $this->getRascalScript($code, $this->getPrinterForName($name));
        $this->assertEquals($expected, $actual, $name);
    }

    public function provideRascalFiles() {
        return $this->getTests(__DIR__ . '/../code/rascal', 'test');
    }

    /**
     * @dataProvider provideRascalFailedFiles
     */
    public function testParseFail($name, $code, $msg) {
        $printer = $this->getPrinterForName($name);

        try {
            $this->parser->parse($code);

            $this->fail(sprintf('"%s": Expected Error', $name));
        } catch (\PhpParser\Error $e) {
            $errorMsg = "errscript(\"" . $printer->rascalizeString($e->getMessage()) . "\")";
            $this->assertEquals($msg, $errorMsg, $name);
        }
    }

    public function provideRascalFailedFiles() {
        return $this->getTests(__DIR__ . '/../code/rascal', 'test-fail');
    }

    /**
     * @param string $code
     * @param \PhpParser\Printer $printer
     * @return string
     */
    protected function getRascalScript($code, $printer)
    {
        $strStmts = $this->getPrettyPrintedParsedCode($code, $printer);
        return "script([" . implode(",\n", $strStmts) . "])";
    }

    /**
     * These names were used during the test code generation
     *
     * @param $name
     * @return string
     */
    private function getFileName($name)
    {
        return sprintf("/tmp/%s.php", $this->normalizeText($name));
    }

    /**
     * @param $name
     * @return AST2Rascal
     */
    private function getPrinterForName($name)
    {
        $fileName = $this->getFileName($name);
        $printer = new AST2Rascal($fileName, false, false, false, "", false);
        return $printer;
    }

    /**
     * @param $code
     * @param $printer
     * @return array
     */
    protected function getPrettyPrintedParsedCode($code, $printer)
    {
        $stmts = $this->parser->parse($code);

        foreach ($stmts as $stmt)
            $strStmts[] = $printer->pprint($stmt);

        return isset($strStmts) ? $strStmts : array();
    }
}
 