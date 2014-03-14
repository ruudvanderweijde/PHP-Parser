<?php

namespace PhpParser;

abstract class CodeTestAbstract extends \PHPUnit_Framework_TestCase
{
    protected function getTests($directory, $fileExtension) {
        $it = new \RecursiveDirectoryIterator($directory);
        $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::LEAVES_ONLY);
        $it = new \RegexIterator($it, '(\.' . preg_quote($fileExtension) . '$)');

        $tests = array();
        foreach ($it as $file) {
            // read file
            $fileContents = file_get_contents($file);

            // evaluate @@{expr}@@ expressions
            $fileContents = preg_replace_callback(
                '/@@\{(.*?)\}@@/',
                array($this, 'evalCallback'),
                $fileContents
            );

            // parse sections
            $parts = array_map('trim', explode('-----', $fileContents));

            // first part is the name
            $name = array_shift($parts);

            // multiple sections possible with always two forming a pair
            foreach (array_chunk($parts, 2) as $chunk) {
                $tests[] = array($name, $chunk[0], $chunk[1]);
            }

            //$this->generateRascalTestsNoParams($file, $name, $parts);
            //$this->generateRascalTestsWithLocations($file, $name, $parts);
        }

        return $tests;
    }

    protected function evalCallback($matches) {
        return eval('return ' . $matches[1] . ';');
    }

    protected function canonicalize($str) {
        // trim from both sides
        $str = trim($str);

        // normalize EOL to \n
        $str = str_replace(array("\r\n", "\r"), "\n", $str);

        // trim right side of all lines
        return implode("\n", array_map('rtrim', explode("\n", $str)));
    }

    protected function generateRascalTestsWithLocations($file, $name, $parts)
    {
        $this->generateRascalTests($file, $name, $parts, 'locations');
    }

    protected function generateRascalTestsNoParams($file, $name, $parts)
    {
        $this->generateRascalTests($file, $name, $parts, 'no_params');
    }

    /**
     * @param $file
     * @param $name
     * @param $parts
     * @return mixed
     */
    protected function generateRascalTests($file, $name, $parts, $type)
    {
            // create test files for ast 2 rascal
            $rascalFileName = str_replace("/parser/", "/rascal/{$type}/", $file);
            $glue = "\n-----\n";
            $rascalFileContent = $name . $glue;
            $first = true;
            foreach (array_chunk($parts, 2) as $chunk) {
                if (!$first)
                    $rascalFileContent .= $glue;

                $first = false;

                $rascalFileContent .= $chunk[0] . $glue;
                $rascalFileContent .= $this->getResultOfParsedPhpCode($chunk[0], $name, $type);

            }
            file_put_contents($rascalFileName, $rascalFileContent);
    }

    /**
     * @param $code
     * @param $name
     * @return string
     */
    protected function getResultOfParsedPhpCode($code, $name, $type) {
        // create a temp file.
        $rscName = $this->normalizeText($name);

        $tempFileName = $this->getFileName($name);
        $tempResultFile = "/tmp/{$rscName}.rsc";

        file_put_contents($tempFileName, $code);
        chmod($tempFileName, "0777");

        $params = ($type === "locations") ? "-l" : "";
        $parserExec = "/usr/local/php5/bin/php -d memory_limit=1024M /Users/ruud/git/PHP-Parser-cwi/lib/Rascal/AST2Rascal.php {$params}";
        $command = sprintf("%s -f%s > %s", $parserExec, $tempFileName, $tempResultFile);
        exec($command);

        $result = file_get_contents($tempResultFile);

        echo "Command: {$command}\n";
        echo "Output: \n{$result}\n\n";

        // remove temp file
        unlink($tempFileName);
        unlink($tempResultFile);

        return $result;

    }

    /**
     * These names were used during the test code generation
     *
     * @param $name
     * @return string
     */
    public function getFileName($name)
    {
        return sprintf("/tmp/%s.php", $this->normalizeText($name));
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function normalizeText($name)
    {
        $name = preg_replace('/[^a-z0-9 ]/i', ' ', $name);
        $name = ucwords(strtolower($name));
        $name = preg_replace('/\s+/', '', $name);
        return $name;
    }

}