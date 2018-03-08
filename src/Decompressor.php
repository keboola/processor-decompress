<?php

namespace Keboola\Processor\Decompress;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Decompressor
{
    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    /**
     * @param string $dataFolder
     * @param SplFileInfo $sourceFile
     * @return string
     */
    private function getDestinationPath(string $dataFolder, SplFileInfo $sourceFile)
    {
        if (!$this->fs->exists($dataFolder . "/out/files/" . $sourceFile->getRelativePathname())) {
            $this->fs->mkdir($dataFolder . "/out/files/" . $sourceFile->getRelativePathname());
        }
        return $dataFolder . "/out/files/" . $sourceFile->getRelativePathname();
    }

    /**
     * @param SplFileInfo $sourceFile
     * @param string $destinationPath
     * @throws Exception
     */
    private function processGzip(SplFileInfo $sourceFile, string $destinationPath)
    {
        try {
            if ($sourceFile->getExtension() != 'gz') {
                $this->fs->rename($sourceFile->getPathname(), $destinationPath . "/" . $sourceFile->getBasename() . '.gz');
            } else {
                $this->fs->rename($sourceFile->getPathname(), $destinationPath . "/" . $sourceFile->getBasename());
            }
            (new Process(
                "gunzip {$destinationPath}/{$sourceFile->getBasename()} -N"
            ))->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception(
                "Failed decompressing file " . $sourceFile->getPathname() . ": " . $e->getMessage()
            );
        }
    }

    /**
     * @param SplFileInfo $sourceFile
     * @param string $destinationPath
     * @throws Exception
     */
    private function processZip(SplFileInfo $sourceFile, string $destinationPath)
    {
        try {
            (new Process(
                "unzip {$sourceFile->getPathname()} -d {$destinationPath}"
            ))->mustRun();
        } catch (ProcessFailedException $e) {
            throw new Exception(
                "Failed decompressing file " . $sourceFile->getPathname() . ": " . $e->getMessage()
            );
        }
    }

    /**
     * @param string $dataFolder
     * @throws Exception
     */
    public function run(string $dataFolder)
    {
        $finder = new Finder();
        $finder->in($dataFolder . "/in/files")->notName('*.manifest')->files();
        foreach ($finder as $sourceFile) {
            $destinationPath = $this->getDestinationPath($dataFolder, $sourceFile);
            if ($sourceFile->getExtension() == 'gz') {
                $this->processGzip($sourceFile, $destinationPath);
            } elseif ($sourceFile->getExtension() == 'zip') {
                $this->processZip($sourceFile, $destinationPath);
            } else {
                try {
                    $this->processZip($sourceFile, $destinationPath);
                } catch (Exception $e) {
                    try {
                        $this->processGzip($sourceFile, $destinationPath);
                    } catch (Exception $ee) {
                        // intentional, file has no extension and is not an archive -> ignore it
                        $this->fs->remove($dataFolder . "/out/files/" . $sourceFile->getRelativePathname());
                    }
                }
            }
        }
    }
}
