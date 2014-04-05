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
    }
}
