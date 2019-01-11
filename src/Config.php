<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress;

class Config extends \Keboola\Component\Config\BaseConfig
{
    public function getCompressionType(): string
    {
        return $this->getValue(['parameters', 'compression_type']);
    }

    public function isGraceful(): bool
    {
        return $this->getValue(['parameters', 'graceful']);
    }
}
