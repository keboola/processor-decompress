<?php

namespace Keboola\Processor\Decompress;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @param string $dataFolder
 * @param SplFileInfo $sourceFile
 * @throws Exception
 */
function decompressZip($dataFolder, SplFileInfo $sourceFile): void
{
    try {
        $destinationPath = getDestinationPath($dataFolder, $sourceFile);
        (new Process(
            "unzip {$sourceFile->getPathname()} -d {$destinationPath}"
        ))
            ->setIdleTimeout(null)
            ->setTimeout(null)
            ->mustRun();
    } catch (ProcessFailedException $e) {
        throw new Exception(
            "Failed decompressing zip file " . $sourceFile->getPathname() . ": " . $e->getMessage()
        );
    }
}
