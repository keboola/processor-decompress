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
    // GZ
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.gz")->in($dataFolder . "/in/files");
    $fs = new \Symfony\Component\Filesystem\Filesystem();
    foreach ($finder as $sourceFile) {
        try {
            $subfolder = substr($sourceFile->getPath(), strlen($dataFolder . 'in/files/'));
            if ($subfolder) {
                if (!$fs->exists($dataFolder . "/out/files" . $subfolder)) {
                    $fs->mkdir($dataFolder . "/out/files" . $subfolder);
                }
            }
            $destinationPath = $dataFolder . "/out/files" . $subfolder . "/" . $sourceFile->getBasename();
            $fs->mkdir($destinationPath);
            $fs->rename($sourceFile->getPathname(), $destinationPath . "/" . $sourceFile->getBasename());
            (new \Symfony\Component\Process\Process(
                "gunzip {$destinationPath}/{$sourceFile->getBasename()} -N"
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
    $finder->name("*.zip")->in($dataFolder . "/in/files");
    foreach ($finder as $sourceFile) {
        try {
            $subfolder = substr($sourceFile->getPath(), strlen($dataFolder . 'in/files/'));
            if ($subfolder) {
                if (!$fs->exists($dataFolder . "/out/files" . $subfolder)) {
                    $fs->mkdir($dataFolder . "/out/files" . $subfolder);
                }
            }
            (new \Symfony\Component\Process\Process(
                "unzip {$sourceFile->getPathname()} -d {$dataFolder}/out/files{$subfolder}/{$sourceFile->getBasename()}"
            ))
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
