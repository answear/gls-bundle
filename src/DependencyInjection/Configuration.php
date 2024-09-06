<?php

declare(strict_types=1);

namespace Answear\GlsBundle\DependencyInjection;

use Answear\GlsBundle\Enum\CountryCodeEnum;
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
                    ->values($this->getCountryCodes())
                    ->isRequired()
                ->end()
                ->scalarNode('logger')->defaultValue(null)->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return string[]
     */
    private function getCountryCodes(): array
    {
        return array_map(
            static fn(CountryCodeEnum $countryCode) => $countryCode->value,
            CountryCodeEnum::cases(),
        );
    }
}
