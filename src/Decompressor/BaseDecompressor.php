<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

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

    public function getDestinationPath(SplFileInfo $sourceFile): string
    {
        $fs = new Filesystem();
        $fs->mkdir($this->destinationFolder . '/' . $sourceFile->getRelativePathname());
        return $this->destinationFolder . '/' . $sourceFile->getRelativePathname();
    }
}
