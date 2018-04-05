<?php

namespace Keboola\Processor\Decompress;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigDefinition implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root("parameters");

        $rootNode
            ->children()
                ->enumNode('compression_type')
                    ->values(['zip', 'gzip'])
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
