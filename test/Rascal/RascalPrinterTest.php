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
        $this->parser = new Parser(new Lexer\Emulative);
    }

    /**
     * @dataProvider provideRascalFilesWithLocations
     */
    public function testParseSuccessWithLocations($name, $code, $expected) {
        $actual = $this->getRascalScript($code, $this->getPrinterForNameWithLocation($name));
        $this->assertEquals($expected, $actual, $name);
    }

    /**
     * @dataProvider provideRascalFilesNoParams
     */
    public function testParseSuccessNoParams($name, $code, $expected) {
        $actual = $this->getRascalScript($code, $this->getPrinterForNameNoParam($name));
        $this->assertEquals($expected, $actual, $name);
    }

    /**
     * @dataProvider provideRascalFailedFilesWithLocations
     */
    public function testParseFailWithLocations($name, $code, $msg) {
        $printer = $this->getPrinterForNameWithLocation($name);

        try {
            $this->parser->parse($code);

            $this->fail(sprintf('"%s": Expected Error', $name));
        } catch (\PhpParser\Error $e) {
            $errorMsg = "errscript(\"" . $printer->rascalizeString($e->getMessage()) . "\")";
            $this->assertEquals($msg, $errorMsg, $name);
        }
    }

    /**
     * @dataProvider provideRascalFailedFilesNoParams
     */
    public function testParseFailNoParams($name, $code, $msg) {
        $printer = $this->getPrinterForNameNoParam($name);

        try {
            $this->parser->parse($code);

            $this->fail(sprintf('"%s": Expected Error', $name));
        } catch (\PhpParser\Error $e) {
            $errorMsg = "errscript(\"" . $printer->rascalizeString($e->getMessage()) . "\")";
            $this->assertEquals($msg, $errorMsg, $name);
        }
    }

    public function provideRascalFilesWithLocations() {
        return $this->provideRascalFiles('locations', 'test');
    }
    public function provideRascalFilesNoParams() {
        return $this->provideRascalFiles('no_params', 'test');
    }
    public function provideRascalFailedFilesWithLocations() {
        return $this->provideRascalFiles('locations', 'test-fail');
    }
    public function provideRascalFailedFilesNoParams() {
        return $this->provideRascalFiles('no_params', 'test-fail');
    }

    public function provideRascalFiles($dir, $fileType) {
        return $this->getTests(__DIR__ . '/../code/rascal/'.$dir, $fileType);
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
     * @param $name
     * @return RascalPrinter
     */
    private function getPrinterForNameNoParam($name)
    {
        $fileName = $this->getFileName($name);
        return new RascalPrinter($fileName, false, false, false, "", false);
    }

    /**
     * @param $name
     * @return RascalPrinter
     */
    private function getPrinterForNameWithLocation($name)
    {
        $fileName = $this->getFileName($name);
        return new RascalPrinter($fileName, true, false, false, "", false);
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
 