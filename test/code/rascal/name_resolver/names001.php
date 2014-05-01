<?php

namespace Test;

use Test\Dummy;
use Test2\Dummy as Dummy2;

class ClassInNameSpaceTest {
    public function __construct() {
        $dummy = new Dummy;
        $dummy->exec();

        $dummy2 = new Dummy2;
        $dummy2->exec();

        $dummy3 = new Dummy2\Dummy3;
        $dummy3->exec();

        $dummy4 = new Dummy2\Dummy3\Dummy4;
        $dummy4->exec();

        $dummy5 = new Dummy5;
        $dummy5->exec();

        $c = new C;
        $c = new \C;
    }
}
