<?php

namespace Happyr\ServiceMocking\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('happyr_service_mocking');

        $treeBuilder->getRootNode()
            ->children()
                ->variableNode('services')->defaultValue([])->end()
            ->end();

        return $treeBuilder;
    }
}
