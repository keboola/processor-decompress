<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress\Decompressor;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class BaseDecompressor
{
    /**
     * @var string
     */
    protected $destinationFolder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $isGraceful;

    public function __construct(string $destinationFolder, LoggerInterface $logger, bool $isGraceful)
    {
        $this->logger = $logger;
        $this->isGraceful = $isGraceful;
        $this->destinationFolder = $destinationFolder;
    }

    public function getDestinationPath(SplFileInfo $sourceFile): string
    {
        $fs = new Filesystem();
        $fs->mkdir($this->destinationFolder . '/' . $sourceFile->getRelativePathname());
        return $this->destinationFolder . '/' . $sourceFile->getRelativePathname();
    }
}
