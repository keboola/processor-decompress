<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Symfony\Component\Finder\SplFileInfo;

interface DecompressorInterface
{
    public function decompress(SplFileInfo $sourceFile): void;
}
