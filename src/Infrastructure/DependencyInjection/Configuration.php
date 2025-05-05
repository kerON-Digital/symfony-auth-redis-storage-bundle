<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('keron_digital_auth_redis_storage');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('redis_client_service_id')
            ->info('Required: The service ID of the Redis client provided by the application.')
            ->isRequired()->cannotBeEmpty()
            ->example('snc_redis.default')
            ->end()

            ->scalarNode('key_prefix')
            ->info('Optional general prefix for all keys managed by this bundle.')
            ->defaultValue('auth:')
            ->cannotBeEmpty()->end()

            ->arrayNode('refresh_token_storage')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('key_suffix')
            ->info('Suffix added to the general prefix for refresh token data keys.')
            ->defaultValue('rt_data:')
            ->cannotBeEmpty()->end()
            ->integerNode('default_ttl')
            ->info('Default TTL in seconds for refresh token data.')
            ->defaultValue(2592000)
            ->end()
            ->end()
            ->end()

            ->arrayNode('blacklist')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('key_suffix')
            ->info('Suffix added to the general prefix for blacklist keys.')
            ->defaultValue('bl:')
            ->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
