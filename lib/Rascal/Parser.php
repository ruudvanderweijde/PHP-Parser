<?php

namespace Rascal;

use PhpParser\Parser;
use PhpParser\Lexer;

require '../bootstrap.php';

ini_set('xdebug.max_nesting_level', 2000);

if (count($argv) < 2) {
    echo "Expected at least 1 argument\n";
    exit() - 1;
}

$opts = getopt("f:lirp:", array(
    "file:",
    "enableLocations",
    "uniqueIds",
    "relativeLocations",
    "prefix:",
    "phpdocs"
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

// always parse phpdocs, or else rascal will crash because it is implemented there unconditional
$addPHPDocs = true;

$parser = new Parser(new Lexer());
$printer = new AST2Rascal($file, $enableLocations, $relativeLocations, $uniqueIds, $prefix, $addPHPDocs);

try {
    $stmts = $parser->parse($inputCode);
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
