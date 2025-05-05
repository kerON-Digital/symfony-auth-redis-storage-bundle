<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class KeronDigitalAuthRedisStorageExtension extends Extension
{
    private const REDIS_CLIENT_PARAM = 'keron_digital.redis_storage.redis_client_id';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../../config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::REDIS_CLIENT_PARAM, $config['redis_client_service_id']);

        $generalPrefix = $config['key_prefix'];

        $blacklistPrefix = $generalPrefix . $config['blacklist']['key_suffix'];
        $container->setParameter('keron_digital.redis_storage.blacklist.prefix', $blacklistPrefix);

        $activePrefix = $generalPrefix . $config['active_token_storage']['key_suffix'];
        $container->setParameter('keron_digital.redis_storage.active_storage.prefix', $activePrefix);
    }

    public function getAlias(): string
    {
        return 'keron_digital_auth_redis_storage';
    }
}
    