<?php

namespace Keboola\Processor\Decompress;

use Symfony\Component\Finder\SplFileInfo;

/**
 *
 * Creates destination folder in `out/files` and keeps the subfolder structure.
 *
 * @param string $dataFolder
 * @param SplFileInfo $sourceFile
 * @return string
 */
function getDestinationPath($dataFolder, SplFileInfo $sourceFile)
{
    $fs = new \Symfony\Component\Filesystem\Filesystem();
    if (!$fs->exists($dataFolder . "/out/files/" . $sourceFile->getRelativePathname())) {
        $fs->mkdir($dataFolder . "/out/files/" . $sourceFile->getRelativePathname());
    }
    return $dataFolder . "/out/files/" . $sourceFile->getRelativePathname();
}
