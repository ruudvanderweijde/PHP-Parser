<?php

if (!class_exists('Autoloader')) {
    require_once __DIR__ . '/../bootstrap.php';
}
$rp = new \Rascal\RascalPrinter("a");
var_dump($rp);
