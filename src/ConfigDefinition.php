<?php

declare(strict_types=1);

namespace Keboola\Processor\Decompress;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigDefinition extends \Keboola\Component\Config\BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        $parametersNode
            ->children()
                ->enumNode('compression_type')
                    ->values(['auto', 'zip', 'gzip', 'snappy'])
                    ->defaultValue('auto')
                ->end()
            ->end()
        ;
        // @formatter:on
        return $parametersNode;
    }
}
