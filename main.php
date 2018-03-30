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
    $fs = new \Symfony\Component\Filesystem\Filesystem();

    // GZ
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.gz")->in($dataFolder . "/in/files")->files();
    foreach ($finder as $sourceFile) {
        try {
            $destinationPath = \Keboola\Processor\Decompress\getDestinationPath($dataFolder, $sourceFile);
            $fs->rename($sourceFile->getPathname(), $destinationPath . "/" . $sourceFile->getBasename());
            (new \Symfony\Component\Process\Process(
                "gunzip {$destinationPath}/{$sourceFile->getBasename()} -N"
            ))
                ->setTimeout(null)
                ->setIdleTimeout(null)
                ->mustRun();
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            throw new \Keboola\Processor\Decompress\Exception(
                "Failed decompressing file " . $sourceFile->getPathname() . ": " . $e->getMessage()
            );
        }
    }

    // ZIP
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.zip")->in($dataFolder . "/in/files")->files();
    foreach ($finder as $sourceFile) {
        try {
            $destinationPath = \Keboola\Processor\Decompress\getDestinationPath($dataFolder, $sourceFile);
            (new \Symfony\Component\Process\Process(
                "unzip {$sourceFile->getPathname()} -d {$destinationPath}"
            ))
                ->setIdleTimeout(null)
                ->setTimeout(null)
                ->mustRun();
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
