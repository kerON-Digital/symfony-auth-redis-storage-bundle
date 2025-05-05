<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class KeronDigitalAuthRedisStorageExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../../config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $generalPrefix = $config['key_prefix'];

        $rtStoragePrefix = $generalPrefix . $config['refresh_token_storage']['key_suffix'];
        $container->setParameter('keron_digital.redis_storage.rt_storage.prefix', $rtStoragePrefix);
        $container->setParameter('keron_digital.redis_storage.rt_storage.default_ttl', $config['refresh_token_storage']['default_ttl']);

        $blacklistPrefix = $generalPrefix . $config['blacklist']['key_suffix'];
        $container->setParameter('keron_digital.redis_storage.blacklist.prefix', $blacklistPrefix);


        $redisClientServiceId = $config['redis_client_service_id'];

        if ($container->hasDefinition('keron_digital.redis_storage.refresh_token_storage')) { // ID de nuestro servicio de storage
            $definition = $container->getDefinition('keron_digital.redis_storage.refresh_token_storage');
            $definition->setArgument('$redisClient', new Reference($redisClientServiceId)); // Inyectar el cliente Redis
            $definition->setArgument('$keyPrefix', $rtStoragePrefix); // Inyectar prefijo específico
            $definition->setArgument('$defaultTtl', '%keron_digital.redis_storage.rt_storage.default_ttl%'); // Inyectar TTL
        }

        if ($container->hasDefinition('keron_digital.redis_storage.blacklist')) { // ID de nuestro servicio de blacklist
            $definition = $container->getDefinition('keron_digital.redis_storage.blacklist');
            $definition->setArgument('$redisClient', new Reference($redisClientServiceId)); // Inyectar el cliente Redis
            $definition->setArgument('$keyPrefix', $blacklistPrefix); // Inyectar prefijo específico
        }

    }

    public function getAlias(): string
    {
        return 'keron_digital_auth_redis_storage';
    }
}
