<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Keboola\Component\UserException;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DecompressorZip extends BaseDecompressor implements DecompressorInterface
{
    public function decompress(SplFileInfo $sourceFile): void
    {
        try {
            $destinationPath = $this->getDestinationPath($sourceFile);
            $process = new Process(['unzip', $sourceFile->getPathname(), '-d', $destinationPath]);
            $process->setIdleTimeout(null)
                ->setTimeout(null)
                ->mustRun();
        } catch (ProcessFailedException $e) {
            if ($this->isGraceful) {
                $this->logger->error($e->getMessage());
            } else {
                throw new UserException(
                    'Failed decompressing zip file ' . $sourceFile->getPathname() . ': ' . $e->getMessage()
                );
            }
        }
    }
}
