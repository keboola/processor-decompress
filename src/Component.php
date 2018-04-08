<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress;

use Keboola\Processor\Decompress\Decompressor\DecompressorGzip;
use Keboola\Processor\Decompress\Decompressor\DecompressorZip;

class Component extends \Keboola\Component\BaseComponent
{

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }

    public function run(): void
    {
        /** @var Config $config */
        $config = $this->getConfig();

        if ($config->getCompressionType() !== 'auto') {
            // force compression type
            if ($config->getCompressionType() == 'gzip') {
                $decompressor = new DecompressorGzip($this->getDataDir() . '/out/files');
            } else {
                $decompressor = new DecompressorZip($this->getDataDir() . '/out/files');
            }

            $finder = new \Symfony\Component\Finder\Finder();
            $finder->notName('*.manifest')->in($this->getDataDir() . '/in/files')->files();
            foreach ($finder as $sourceFile) {
                $decompressor->decompress($sourceFile);
            }
        } else {
            // detect compression types by extension
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->notName('*.gz')->notName('*.zip')->notName('*.manifest')->in($this->getDataDir() . '/in/files')->files();
            foreach ($finder as $sourceFile) {
                throw new \Keboola\Processor\Decompress\Exception(
                    'File ' . $sourceFile->getPathname() . ' is not an archive.'
                );
            }

            // GZ
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->name('*.gz')->in($this->getDataDir() . '/in/files')->files();
            $gzipDecompressor = new DecompressorGzip($this->getDataDir() . '/out/files');
            foreach ($finder as $sourceFile) {
                $gzipDecompressor->decompress($sourceFile);
            }

            // ZIP
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->name('*.zip')->in($this->getDataDir() . '/in/files')->files();
            $zipDecompressor = new DecompressorZip($this->getDataDir() . '/out/files');
            foreach ($finder as $sourceFile) {
                $zipDecompressor->decompress($sourceFile);
            }
        }
    }
}
