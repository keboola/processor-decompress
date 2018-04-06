<?php declare(strict_types = 1);

namespace Keboola\Processor\Decompress\Decompressor;

use Keboola\Processor\Decompress\Exception;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DecompressorGzip extends BaseDecompressor implements DecompressorInterface
{
    /**
     * @param SplFileInfo $sourceFile
     * @throws Exception
     */
    public function decompress(SplFileInfo $sourceFile): void
    {
        try {
            $baseName = $sourceFile->getBasename();

            // strip .gz suffix if present
            if (substr($baseName, -3) === '.gz') {
                $baseName = substr($baseName, 0, -3);
            }

            $destinationPath = $this->getDestinationPath($sourceFile);
            (new Process(
                "gunzip -c {$sourceFile->getPathname()} > {$destinationPath}/{$baseName}"
            ))
                ->setTimeout(null)
                ->setIdleTimeout(null)
                ->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception(
                'Failed decompressing gzip file ' . $sourceFile->getPathname() . ': ' . $e->getMessage()
            );
        }
    }
}
