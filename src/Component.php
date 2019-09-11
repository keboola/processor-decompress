<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Keboola\Processor\Decompress\Decompressor\DecompressorGzip;
use Keboola\Processor\Decompress\Decompressor\DecompressorSnappy;
use Keboola\Processor\Decompress\Decompressor\DecompressorZip;
use Keboola\Processor\Decompress\Decompressor\DecompressorZlib;
use Symfony\Component\Finder\Finder;

class Component extends BaseComponent
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
            switch ($config->getCompressionType()) {
                case 'gzip':
                    $decompressor = new DecompressorGzip(
                        $this->getDataDir() . '/out/files',
                        $this->getLogger(),
                        $config->isGraceful()
                    );
                    break;
                case 'snappy':
                    $decompressor = new DecompressorSnappy(
                        $this->getDataDir() . '/out/files',
                        $this->getLogger(),
                        $config->isGraceful()
                    );
                    break;
                case 'zlib':
                    $decompressor = new DecompressorZlib(
                        $this->getDataDir() . '/out/files',
                        $this->getLogger(),
                        $config->isGraceful(),
                        $config->getZlibWindowSize()
                    );
                    break;
                default:
                    $decompressor = new DecompressorZip(
                        $this->getDataDir() . '/out/files',
                        $this->getLogger(),
                        $config->isGraceful()
                    );
            }

            $finder = new Finder();
            $finder->notName('*.manifest')->in($this->getDataDir() . '/in/files')->files();
            foreach ($finder as $sourceFile) {
                $decompressor->decompress($sourceFile);
            }
        } else {
            // detect compression types by extension
            $finder = new Finder();
            $finder->notName('*.gz')
                ->notName('*.zip')
                ->notName('*.snappy')
                ->notName('*.manifest')
                ->in($this->getDataDir() . '/in/files')->files();
            foreach ($finder as $sourceFile) {
                if ($config->isGraceful()) {
                    $this->getLogger()->error('File "' . $sourceFile->getPathname() . '" is not an archive.');
                } else {
                    throw new UserException(
                        'File "' . $sourceFile->getPathname() . '" is not an archive.'
                    );
                }
            }

            // GZ
            $finder = new Finder();
            $finder->name('*.gz')->in($this->getDataDir() . '/in/files')->files();
            $gzipDecompressor = new DecompressorGzip(
                $this->getDataDir() . '/out/files',
                $this->getLogger(),
                $config->isGraceful()
            );
            foreach ($finder as $sourceFile) {
                $gzipDecompressor->decompress($sourceFile);
            }

            // ZIP
            $finder = new Finder();
            $finder->name('*.zip')->in($this->getDataDir() . '/in/files')->files();
            $zipDecompressor = new DecompressorZip(
                $this->getDataDir() . '/out/files',
                $this->getLogger(),
                $config->isGraceful()
            );
            foreach ($finder as $sourceFile) {
                $zipDecompressor->decompress($sourceFile);
            }

            // Snappy
            $finder = new Finder();
            $finder->name('*.snappy')->in($this->getDataDir() . '/in/files')->files();
            $zipDecompressor = new DecompressorSnappy(
                $this->getDataDir() . '/out/files',
                $this->getLogger(),
                $config->isGraceful()
            );
            foreach ($finder as $sourceFile) {
                $zipDecompressor->decompress($sourceFile);
            }
        }
    }
}
