<?php

declare(strict_types=1);

namespace Answear\GlsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('answear_gls');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('logger')->defaultValue(null)->end()
            ->end();

        return $treeBuilder;
    }
}
