<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Keboola\Component\UserException;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DecompressorSnappy extends BaseDecompressor implements DecompressorInterface
{
    public function decompress(SplFileInfo $sourceFile): void
    {
        try {
            $baseName = $sourceFile->getBasename('.snappy');
            $destinationPath = $this->getDestinationPath($sourceFile);
            $process = new Process([
                'python3',
                '-m',
                'snappy',
                '-d',
                $sourceFile->getPathname(),
                $destinationPath . '/' . $baseName,
            ]);
            $process->setTimeout(null)
                ->setIdleTimeout(null)
                ->mustRun();
        } catch (ProcessFailedException $e) {
            throw new UserException(
                'Failed decompressing snappy file ' . $sourceFile->getPathname() . ': ' . $e->getMessage()
            );
        }
    }
}
