<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Keboola\Processor\Decompress\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BaseDecompressor
{
    /**
     * @var string
     */
    protected $destinationFolder;

    public function __construct(string $destinationFolder)
    {
        $this->destinationFolder = $destinationFolder;
    }

    public function getDestinationPath(SplFileInfo $sourceFile) : string
    {
        $fs = new Filesystem();
        if (!$fs->exists($this->destinationFolder . '/' . $sourceFile->getRelativePathname())) {
            $fs->mkdir($this->destinationFolder . '/' . $sourceFile->getRelativePathname());
        }
        return $this->destinationFolder . '/' . $sourceFile->getRelativePathname();
    }
}
