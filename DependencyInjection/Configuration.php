<?php


namespace IMA\Bundle\keycloakBearerOnlyAdapterBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder("ima_keycloak_bearer_only_adapter");

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode("issuer")
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("realm")
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("client_id")
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("client_secret")
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('ssl_verification')
                    ->defaultFalse()
                    ->treatNullLike(false)
                ->end();

        return $treeBuilder;
    }
}