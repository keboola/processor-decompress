<?php declare(strict_types = 1);

namespace Keboola\Processor\Decompress;

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
            switch ($config->getCompressionType()) {
                case 'gzip':
                    $decompressFunction = '\Keboola\Processor\Decompress\decompressGzip';
                    break;
                case 'zip':
                    $decompressFunction = '\Keboola\Processor\Decompress\decompressZip';
                    break;
                default:
                    $decompressFunction = function () use ($config) {
                        throw new \Keboola\Processor\Decompress\Exception(
                            "Unknown compression type {$config->getCompressionType()}"
                        );
                    };
            }

            $finder = new \Symfony\Component\Finder\Finder();
            $finder->notName("*.manifest")->in($this->getDataDir() . "/in/files")->files();
            foreach ($finder as $sourceFile) {
                $decompressFunction($this->getDataDir(), $sourceFile);
            }
        } else {
            // detect compression types by extension
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->notName("*.gz")->notName("*.zip")->notName("*.manifest")->in($this->getDataDir() . "/in/files")->files();
            foreach ($finder as $sourceFile) {
                throw new \Keboola\Processor\Decompress\Exception(
                    "File " . $sourceFile->getPathname() . " is not an archive."
                );
            }

            // GZ
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->name("*.gz")->in($this->getDataDir() . "/in/files")->files();
            foreach ($finder as $sourceFile) {
                \Keboola\Processor\Decompress\decompressGzip($this->getDataDir(), $sourceFile);
            }

            // ZIP
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->name("*.zip")->in($this->getDataDir() . "/in/files")->files();
            foreach ($finder as $sourceFile) {
                \Keboola\Processor\Decompress\decompressZip($this->getDataDir(), $sourceFile);
            }
        }
    }
}
