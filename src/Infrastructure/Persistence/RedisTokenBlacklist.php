<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\Persistence;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\TokenBlacklistInterface;
use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;
use Redis;
use RedisException;
use Throwable;

/**
 * Redis implementation for the TokenBlacklistInterface.
 */
final class RedisTokenBlacklist implements TokenBlacklistInterface
{
    private Redis $redisClient;
    private string $keyPrefix;

    /**
     * @param Redis $redisClient The configured Redis client instance.
     * @param string $keyPrefix The prefix for blacklist keys (e.g., "auth:bl:").
     */
    public function __construct(
        Redis $redisClient,
        string $keyPrefix
    ) {
        $this->redisClient = $redisClient;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function blacklist(string $tokenId, int $remainingTtl): void
    {
        if ($remainingTtl <= 0) {
            return;
        }
        $key = $this->keyPrefix . $tokenId;
        $value = '1';

        try {
            $result = $this->redisClient->setex($key, $remainingTtl, $value);

            if ($result === false) {
                throw new RedisException('SETEX command failed or returned false.');
            }
        } catch (Throwable $e) {
            throw StorageException::forSave($key, $e->getMessage(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isBlacklisted(string $tokenId): bool
    {
        $key = $this->keyPrefix . $tokenId;

        try {
            $exists = $this->redisClient->exists($key);

            return (int) $exists > 0;
        } catch (Throwable $e) {
            throw StorageException::forRead($key, $e->getMessage(), $e);
        }
    }
}
