<?php

namespace Rascal;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rascal\NodeVisitor\NameResolver as NameResolverRascal;
use PhpParser\Parser;
use PhpParser\Lexer;

class AST2RascalTest extends \PHPUnit_Framework_TestCase {
    protected $baseFolder;
    protected $parser;
    protected $traverser;

    private $fileName;
    private $parseTree;

    public function setUp() {
        $this->baseFolder = __DIR__ . "/../code/rascal/name_resolver/";

        $this->parser = new Parser(new Lexer\Emulative);

        $this->traverser = new NodeTraverser;
        $this->traverser->addVisitor(new NameResolver);
        $this->traverser->addVisitor(new NameResolverRascal);
    }

    /**
     * @dataProvider getTestData
     */
    public function testNameResolution($fileName, $expectedPre, $expectedPost) {
        $this->fileName = $fileName;
        $this->parseTree = $this->parserFile();

        $this->assertClassNames($expectedPre);

        // Now: resolve the class names to full names
        $this->traverser->traverse($this->parseTree);

        $this->assertClassNames($expectedPost);
    }

    public function parserFile() {
        $inputCode = file_get_contents($this->baseFolder.$this->fileName);

        return $this->parser->parse($inputCode);
    }

    private function getPrinter() {
        return new RascalPrinter(
            $this->fileName,
            $enableLocations = true,
            $relativeLocations = false,
            $uniqueIds = true,
            $prefix = true,
            $addPHPDocs = true,
            $enableLocationInfo = true
        );
    }

    /**
     * @param $parseTree
     * @param $matches
     */
    private function assertClassNames($expected)
    {
        $pp = "";
        foreach ($this->parseTree as $parseTree) {
            $pp .= $this->getPrinter()->pprint($parseTree);
        }

        if (preg_match_all("/new\(name\(name\(\"([^\"]*?)\"\)/", $pp, $matches)) {
            $this->assertEquals($matches[1], $expected);
        }
    }

    public function getTestData()
    {
        return array(
            array(
                //use Test\Dummy;
                //use Test2\Dummy as Dummy2;
                "file" => "names001.php",
                "preTraverse" => array(
                    "Dummy",                            //new Dummy;
                    "Dummy2",                           //new Dummy2;
                    "Dummy2::Dummy3",                   //new Dummy2\Dummy3;
                    "Dummy2::Dummy3::Dummy4",           //new Dummy2\Dummy3\Dummy4;
                    "Dummy5",                           //new Dummy5;
                ),
                "postTraverse" => array(
                    "Test::Dummy",                      //new Dummy;
                    "Test2::Dummy",                     //new Dummy2;
                    "Test2::Dummy::Dummy3",             //new Dummy2\Dummy3;
                    "Test2::Dummy::Dummy3::Dummy4",     //new Dummy2\Dummy3\Dummy4;
                    "Test::Dummy5",                     //new Dummy5;
                ),
            ),
            array(
                //use Animal\Bear;
                //use Car\Panda;
                "file" => "names002.php",
                "preTraverse" => array(
                    "Bear",                             //new Bear;
                    "Bear::Panda",                      //new Bear\Panda;
                    "Bear::Panda::GiantPanda",          //new Bear\Panda\GiantPanda;
                    "Animal::Bear::Panda::GiantPanda",  //new \Animal\Bear\Panda\GiantPanda;
                    "Panda",                            //new Panda;
                    "Car::Panda",                       //new \Car\Panda;
                ),
                "postTraverse" => array(
                    "Animal::Bear",                     //new Bear;
                    "Animal::Bear::Panda",              //new Bear\Panda;
                    "Animal::Bear::Panda::GiantPanda",  //new Bear\Panda\GiantPanda;
                    "Animal::Bear::Panda::GiantPanda",  //new \Animal\Bear\Panda\GiantPanda;
                    "Car::Panda",                       //new Panda;
                    "Car::Panda",                       //new \Car\Panda;
                ),
            ),

        );
    }
}
 