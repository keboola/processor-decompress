<?php declare(strict_types = 1);

namespace Keboola\Processor\Decompress\Decompressor;

use Symfony\Component\Finder\SplFileInfo;

interface DecompressorInterface
{
    /**
     * @param SplFileInfo $sourceFile
     * @throws Exception
     */
    public function decompress(SplFileInfo $sourceFile): void;
}
