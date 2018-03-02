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

/**
 *
 * Creates destination folder in `out/files` and keeps the subfolder structure.
 *
 * @param string $dataFolder
 * @param SplFileInfo $sourceFile
 * @return string
 */
function getDestinationPath(string $dataFolder, \Symfony\Component\Finder\SplFileInfo $sourceFile)
{
    $fs = new \Symfony\Component\Filesystem\Filesystem();
    if (!$fs->exists($dataFolder . "/out/files/" . $sourceFile->getRelativePathname())) {
        $fs->mkdir($dataFolder . "/out/files/" . $sourceFile->getRelativePathname());
    }
    return $dataFolder . "/out/files/" . $sourceFile->getRelativePathname();
}

try {
    $fs = new \Symfony\Component\Filesystem\Filesystem();

    // GZ
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->name("*.gz")->in($dataFolder . "/in/files")->files();
    foreach ($finder as $sourceFile) {
        try {
            $destinationPath = getDestinationPath($dataFolder, $sourceFile);
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
    $finder->name("*.zip")->in($dataFolder . "/in/files")->files();
    foreach ($finder as $sourceFile) {
        try {
            $destinationPath = getDestinationPath($dataFolder, $sourceFile);
            (new \Symfony\Component\Process\Process(
                "unzip {$sourceFile->getPathname()} -d {$destinationPath}"
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
