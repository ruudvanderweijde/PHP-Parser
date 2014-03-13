<?php

namespace Rascal;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\Lexer;

if (!class_exists('Autoloader'))
    require_once __DIR__ . '/../bootstrap.php';

ini_set('xdebug.max_nesting_level', 2000);

if (count($argv) < 2) {
    echo "Expected at least 1 argument\n";
    exit() - 1;
}

if (!isset($opts))
    $opts = getopt("f:lirp:", array(
        "file:",
        "enableLocations",
        "uniqueIds",
        "relativeLocations",
        "prefix:",
        "phpdocs",
        "resolve-namespaces"
    ));

if (isset($opts["f"]))
    $file = $opts["f"];
else
    if (isset($opts["file"]))
        $file = $opts["file"];
    else
        if (count($argv) == 2) {
            $file = $argv[1];
        } else {
            echo "errscript(\"The file must be provided using either -f or --file\")";
            exit() - 1;
        }

$enableLocations = false;
if (isset($opts["l"]) || isset($opts["enableLocations"]))
    $enableLocations = true;

$uniqueIds = false;
if (isset($opts["i"]) || isset($opts["uniqueIds"]))
    $uniqueIds = true;

if (isset($opts["p"]))
    $prefix = $opts["p"] . '.';
else
    if (isset($opts["prefix"]))
        $prefix = $opts["prefix"] . '.';
    else {
        $prefix = "";
    }

$relativeLocations = false;
if (isset($opts["r"]) || isset($opts["relativelocations"]))
    $relativeLocations = true;

$addPHPDocs = false;
if (isset($opts["phpdocs"]))
  $addPHPDocs = true;

if (isset($_SERVER['HOME'])) {
    $homedir = $_SERVER['HOME'];
} else {
    $homedir = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
}

$inputCode = '';
if (! $relativeLocations && file_exists($file))
    $inputCode = file_get_contents($file);
else
    if ($relativeLocations && file_exists($homedir . $file))
        $inputCode = file_get_contents($homedir . $file);
    else {
        echo "errscript(\"The given file, $file, does not exist\")";
        exit() - 1;
    }

$resolveNamespaces = isset($opts['resolve-namespaces']) ? true : false;

$parser = new Parser(new Lexer\Emulative);
$printer = new RascalPrinter($file, $enableLocations, $relativeLocations, $uniqueIds, $prefix, $addPHPDocs);

try {
    $stmts = $parser->parse($inputCode);

    if ($resolveNamespaces) {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);
        $traverser->traverse($stmts);
    }

    $strStmts = array();
    foreach ($stmts as $stmt)
        $strStmts[] = $printer->pprint($stmt);
    $script = implode(",\n", $strStmts);
    echo "script([" . $script . "])";
} catch (\PhpParser\Error $e) {
    echo "errscript(\"" . $printer->rascalizeString($e->getMessage()) . "\")";
} catch (\Exception $e) {
    echo "errscript(\"" . $printer->rascalizeString($e->getMessage()) . "\")";
}
?>
