<?php

namespace Keboola\Processor\Decompress;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @param string $dataFolder
 * @param SplFileInfo $sourceFile
 * @throws Exception
 */
function decompressGzip($dataFolder, SplFileInfo $sourceFile): void
{
    try {
        $fs = new Filesystem();
        $destinationPath = getDestinationPath($dataFolder, $sourceFile);
        $fs->mkdir($destinationPath);
        $baseName = $sourceFile->getBasename();

        // strip .gz suffix if present
        if (substr($baseName, -3) === '.gz') {
            $baseName = substr($baseName, 0, -3);
        }

        (new Process(
            "gunzip -c {$sourceFile->getPathname()} > {$destinationPath}/{$baseName}"
        ))
            ->setTimeout(null)
            ->setIdleTimeout(null)
            ->mustRun();
    } catch (ProcessFailedException $e) {
        throw new Exception(
            "Failed decompressing gzip file " . $sourceFile->getPathname() . ": " . $e->getMessage()
        );
    }
}
