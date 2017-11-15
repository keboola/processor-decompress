<?php
// Catch all warnings and notices
set_error_handler(
    function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);
require __DIR__ . "/vendor/autoload.php";

use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

$arguments = getopt("", ["data:"]);
if (!isset($arguments["data"])) {
    $dataFolder = "/data";
} else {
    $dataFolder = $arguments["data"];
}

try {

    // GZ
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.gz")->in($dataFolder . "/in/files")->depth("<= 1");
    $fs = new \Symfony\Component\Filesystem\Filesystem();
    foreach ($finder as $sourceFile) {
        try {
            $subfolder = substr($sourceFile->getPath(), strlen($dataFolder . 'in/files/'));
            if ($subfolder) {
                if (!$fs->exists($dataFolder . "/out/files" . $subfolder)) {
                    $fs->mkdir($dataFolder . "/out/files" . $subfolder);
                }
            }
            (new \Symfony\Component\Process\Process(
                "gunzip {$sourceFile->getPathname()} --stdout > {$dataFolder}/out/files{$subfolder}/" . substr(
                    $sourceFile->getBasename(),
                    0,
                    -3
                )
            ))
                ->mustRun();
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            throw new \Keboola\Processor\Decompress\Exception(
                "Failed decompressing file " . $sourceFile->getPathname() . ": " . $e->getMessage()
            );
        }
    }

    // ZIP
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.zip")->in($dataFolder . "/in/files")->depth(0);
    foreach ($finder as $sourceFile) {
        try {
            (new \Symfony\Component\Process\Process(
                "unzip -j {$sourceFile->getPathname()} -d {$dataFolder}/out/files/{$sourceFile->getBasename()}"
            )
            )->mustRun();
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            throw new \Keboola\Processor\Decompress\Exception(
                "Failed decompressing file " . $sourceFile->getPathname() . ": " . $e->getMessage()
            );
        }
    }
} catch (\Keboola\Processor\Decompress\Exception $e) {
    echo $e->getMessage();
    exit(1);
}
