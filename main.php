<?php
// Catch all warnings and notices
set_error_handler(
    function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);
require __DIR__ . "/vendor/autoload.php";

$arguments = getopt("", ["data:"]);
if (!isset($arguments["data"])) {
    $dataFolder = "/data";
} else {
    $dataFolder = $arguments["data"];
}

try {
    $decompressor = new \Keboola\Processor\Decompress\Decompressor();
    $decompressor->run($dataFolder);
} catch (\Keboola\Processor\Decompress\Exception $e) {
    echo $e->getMessage();
    exit(1);
}
