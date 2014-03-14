<?php

namespace Rascal;

class AST2RascalTest extends \PHPUnit_Framework_TestCase {
    const TEMP_FILE_NAME = "/tmp/ns.php";

    const SOURCE_CODE = <<<CODE
<?php
namespace Animal { class Ape {} }

namespace Animal { class Bear {} }
namespace Animal\Bear { class Panda extends \Animal\Bear {} }

namespace City { class Bear {} }

namespace Car { class Panda {} }

namespace {
    use Animal\Bear;
    use City\Bear as CityBear;

    use \Animal\Bear\Panda as PandaBear;
    use \Car\Panda;

    echo "Animal\\Bear = " . get_class(new Bear) . "\\n";
    echo "Animal\\Bear = " . get_class(new Animal\Bear) . "\\n";
    echo "Animal\\Bear = " . get_class(new \Animal\Bear) . "\\n";
    echo "-------------------\\n";
    echo "City\\Bear = " . get_class(new CityBear) . "\\n";
    echo "City\\Bear = " . get_class(new City\Bear) . "\\n";
    echo "City\\Bear = " . get_class(new \City\Bear) . "\\n";
    echo "-------------------\\n";
    echo "Animal\\Bear\\Panda = " . get_class(new PandaBear) . "\\n";
    echo "Animal\\Bear\\Panda = " . get_class(new Bear\Panda) . "\\n";
    echo "Animal\\Bear\\Panda = " . get_class(new Animal\Bear\Panda) . "\\n";
    echo "Animal\\Bear\\Panda = " . get_class(new \Animal\Bear\Panda) . "\\n";
    echo "-------------------\\n";
    echo "Car\\Panda = " . get_class(new Panda) . "\\n";
    echo "Car\\Panda = " . get_class(new Car\Panda) . "\\n";
    echo "Car\\Panda = " . get_class(new \Car\Panda) . "\\n";
}
CODE;

    const PARSED_RESULT_WITH_RESOLVED_NAMESPACES = 'script([namespace(someName(name("Animal")),[classDef(class("Ape",{},noName(),[],[]))]),
namespace(someName(name("Animal")),[classDef(class("Bear",{},noName(),[],[]))]),
namespace(someName(name("Animal::Bear")),[classDef(class("Panda",{},someName(name("Animal::Bear")),[],[]))]),
namespace(someName(name("City")),[classDef(class("Bear",{},noName(),[],[]))]),
namespace(someName(name("Car")),[classDef(class("Panda",{},noName(),[],[]))]),
namespace(noName(),[use([use(name("Animal::Bear"),someName(name("Bear")))]),use([use(name("City::Bear"),someName(name("CityBear")))]),use([use(name("Animal::Bear::Panda"),someName(name("PandaBear")))]),use([use(name("Car::Panda"),someName(name("Panda")))]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([scalar(string("-------------------\\n"))]),echo([binaryOperation(binaryOperation(scalar(string("City\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("City::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("City\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("City::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("City\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("City::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([scalar(string("-------------------\\n"))]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([scalar(string("-------------------\\n"))]),echo([binaryOperation(binaryOperation(scalar(string("Car\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Car::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Car\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Car::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Car\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Car::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())])])])';

    const PARSED_RESULT_WITHOUT_RESOLVED_NAMESPACES = 'script([namespace(someName(name("Animal")),[classDef(class("Ape",{},noName(),[],[]))]),
namespace(someName(name("Animal")),[classDef(class("Bear",{},noName(),[],[]))]),
namespace(someName(name("Animal::Bear")),[classDef(class("Panda",{},someName(name("Animal::Bear")),[],[]))]),
namespace(someName(name("City")),[classDef(class("Bear",{},noName(),[],[]))]),
namespace(someName(name("Car")),[classDef(class("Panda",{},noName(),[],[]))]),
namespace(noName(),[use([use(name("Animal::Bear"),someName(name("Bear")))]),use([use(name("City::Bear"),someName(name("CityBear")))]),use([use(name("Animal::Bear::Panda"),someName(name("PandaBear")))]),use([use(name("Car::Panda"),someName(name("Panda")))]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([scalar(string("-------------------\\n"))]),echo([binaryOperation(binaryOperation(scalar(string("City\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("CityBear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("City\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("City::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("City\\\\Bear = ")),call(name(name("get_class")),[actualParameter(new(name(name("City::Bear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([scalar(string("-------------------\\n"))]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("PandaBear")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Bear::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Animal\\\\Bear\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Animal::Bear::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([scalar(string("-------------------\\n"))]),echo([binaryOperation(binaryOperation(scalar(string("Car\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Car\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Car::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())]),echo([binaryOperation(binaryOperation(scalar(string("Car\\\\Panda = ")),call(name(name("get_class")),[actualParameter(new(name(name("Car::Panda")),[]),false)]),concat()),scalar(string("\\n")),concat())])])])';

    public static function setUpBeforeClass() {
        file_put_contents(self::TEMP_FILE_NAME, self::SOURCE_CODE);
        chmod(self::TEMP_FILE_NAME, "0777");
    }

    public static function tearDownAfterClass() {
        unlink(self::TEMP_FILE_NAME);
    }

    public function testAST2Racal() {
        $output = $this->getParsedTempFile();
        $this->assertEquals(self::PARSED_RESULT_WITHOUT_RESOLVED_NAMESPACES, $output);
    }

    public function testAST2RacalResolveNamespaces() {
        $output = $this->getParsedTempFileWithResolvedNamespaces();
        $this->assertEquals(self::PARSED_RESULT_WITH_RESOLVED_NAMESPACES, $output);
    }

    /**
     * @return string
     */
    protected function getParsedTempFile()
    {
        $output = $this->getOutput();
        return $output;
    }

    /**
     * @return string
     */
    protected function getParsedTempFileWithResolvedNamespaces()
    {
        $opts = array(
            'resolve-namespaces' => true,
        );
        $output = $this->getOutput($opts);

        return $output;
    }

    /**
     * @param array $opts
     * @return string
     */
    protected function getOutput($opts = null)
    {
        $argv = array(
            0 => __DIR__ . '/../../lib/Rascal/AST2Rascal.php',
            1 => self::TEMP_FILE_NAME,
        );

        if (is_null($opts))
            unset($opts);

        ob_start();
        require(__DIR__ . '/../../lib/Rascal/AST2Rascal.php');
        $output = ob_get_clean();
        unset($argv, $opts);
        return $output;
    }

}
