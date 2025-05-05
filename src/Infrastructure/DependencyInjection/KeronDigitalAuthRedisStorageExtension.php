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

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../../config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::REDIS_CLIENT_PARAM, $config['redis_client_service_id']);

        $generalPrefix = $config['key_prefix'];

        $blacklistConfig = $config['blacklist'] ?? [];
        $blacklistSuffix = $blacklistConfig['key_suffix'] ?? 'bl:';
        $blacklistPrefix = $generalPrefix . $blacklistSuffix;
        $container->setParameter('keron_digital.redis_storage.blacklist.prefix', $blacklistPrefix);

        $activeStorageConfig = $config['active_token_storage'] ?? [];
        $activeSuffix = $activeStorageConfig['key_suffix'] ?? 'active:';
        $activePrefix = $generalPrefix . $activeSuffix;

        $container->setParameter('keron_digital.redis_storage.active_token_storage.prefix', $activePrefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'keron_digital_auth_redis_storage';
    }
}
