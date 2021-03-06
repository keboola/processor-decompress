<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Keboola\Component\UserException;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DecompressorGzip extends BaseDecompressor implements DecompressorInterface
{
    public function decompress(SplFileInfo $sourceFile): void
    {
        try {
            $baseName = $sourceFile->getBasename('.gz');
            $destinationPath = $this->getDestinationPath($sourceFile);
            $process = Process::fromShellCommandline(
                'gunzip -c ' .
                escapeshellarg($sourceFile->getPathname()) .' > ' .
                escapeshellarg($destinationPath . '/' . $baseName)
            );
            $process->setTimeout(null)
                ->setIdleTimeout(null)
                ->mustRun();
        } catch (ProcessFailedException $e) {
            if ($this->isGraceful) {
                $this->logger->error($e->getMessage());
            } else {
                throw new UserException(
                    'Failed decompressing gzip file ' . $sourceFile->getPathname() . ': ' . $e->getMessage()
                );
            }
        }
    }
}
