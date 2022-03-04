<?php

declare(strict_types=1);

namespace Answear\GlsBundle\DependencyInjection;

use Answear\GlsBundle\Enum\CountryCode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('answear_gls');

        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('countryCode')
                    ->values(CountryCode::getValues())
                    ->isRequired()
                ->end()
                ->scalarNode('logger')->defaultValue(null)->end()
            ->end();

        return $treeBuilder;
    }
}
