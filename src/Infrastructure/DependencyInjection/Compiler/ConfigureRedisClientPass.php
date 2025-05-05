<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Configures the Redis client dependency for storage services
 * only if a valid service ID is provided in the configuration.
 */
final class ConfigureRedisClientPass implements CompilerPassInterface
{
    private const PLACEHOLDER_SERVICE_ID = 'CHANGE_ME_redis_service_id';

    private const TARGET_SERVICE_IDS = [
        'keron_digital.redis_storage.blacklist',
        'keron_digital.redis_storage.active_token_storage',
    ];

    private const REDIS_CLIENT_PARAM = 'keron_digital.redis_storage.redis_client_id';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(self::REDIS_CLIENT_PARAM)) {
            return;
        }

        $redisClientId = $container->getParameter(self::REDIS_CLIENT_PARAM);

        if (empty($redisClientId) || self::PLACEHOLDER_SERVICE_ID === $redisClientId) {
            return;
        }

        if (!$container->has($redisClientId)) {
            throw new ServiceNotFoundException($redisClientId, 'KeronDigitalAuthRedisStorageBundle configuration');
        }

        $redisClientReference = new Reference($redisClientId);

        foreach (self::TARGET_SERVICE_IDS as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $definition = $container->getDefinition($serviceId);
                $definition->replaceArgument(0, $redisClientReference);
            }
        }
    }
}
    