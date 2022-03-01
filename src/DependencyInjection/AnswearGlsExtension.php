<?php

declare(strict_types=1);

namespace Answear\GlsBundle\DependencyInjection;

use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Logger\GlsLogger;
use Psr\Log\NullLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AnswearGlsExtension extends Extension implements PrependExtensionInterface
{
    private ?Definition $loggerDefinition;
    private array $config;

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $this->setConfig($container, $configs);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $this->setConfig($container, $configs);

        $this->setConfigProvider($container);
        $this->setLogger($container);
    }

    private function setConfigProvider(ContainerBuilder $container): void
    {
        //$definition = $container->getDefinition(ConfigProvider::class);
        //$definition->setArguments([$environment, $apiKey]);
    }

    private function setLogger(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(GlsLogger::class);
        $definition->setArguments(
            [$this->loggerDefinition ?? new NullLogger()]
        );
    }

    private function setConfig(ContainerBuilder $container, array $configs): void
    {
        if (isset($this->config)) {
            return;
        }

        $configuration = $this->getConfiguration($configs, $container);
        $this->config = $this->processConfiguration($configuration, $configs);

        if (null !== $this->config['logger']) {
            $this->loggerDefinition = $container->getDefinition($this->config['logger']);
        }
    }
}
