<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DecompressorZlib extends BaseDecompressor implements DecompressorInterface
{
    /**
     * @var int
     */
    private $windowSize;

    public function __construct(string $destinationFolder, LoggerInterface $logger, bool $isGraceful, int $windowSize)
    {
        parent::__construct($destinationFolder, $logger, $isGraceful);
        $this->windowSize = $windowSize;
    }

    public function decompress(SplFileInfo $sourceFile): void
    {
        try {
            $baseName = $sourceFile->getBasename();
            $destinationPath = $this->getDestinationPath($sourceFile);

            $process = new Process([
                'python3',
                '/code/script/zlib.decompress.py',
                $sourceFile->getPathname(),
                $this->windowSize,
                $destinationPath . '/' . $baseName,
            ]);
            $process->setTimeout(null)
                ->setIdleTimeout(null)
                ->mustRun();
        } catch (ProcessFailedException $e) {
            if ($this->isGraceful) {
                $this->logger->error($e->getMessage());
            } else {
                throw new UserException(
                    'Failed decompressing zlib file ' . $sourceFile->getPathname() . ': ' . $e->getMessage()
                );
            }
        }
    }
}
