<?php

namespace IMA\Bundle\keycloakBearerOnlyAdapterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IMAkeycloakBearerOnlyAdapterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('ima_keycloak_bearer_only_adapter.keycloak_bearer_user_provider');
        $definition->replaceArgument(0, $config['issuer']);
        $definition->replaceArgument(1, $config['realm']);
        $definition->replaceArgument(2, $config['client_id']);
        $definition->replaceArgument(3, $config['client_secret']);
        $definition->replaceArgument(4, $config['ssl_verification']);
    }

    public function getAlias(): string
    {
        return 'ima_keycloak_bearer_only_adapter';
    }
}
