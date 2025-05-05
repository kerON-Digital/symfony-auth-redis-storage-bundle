<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\Persistence;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\ActiveTokenStorageInterface;
use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;
use Redis;
use RedisException;
use Throwable;

/**
 * Redis implementation for the ActiveTokenStorageInterface.
 */
final class RedisActiveTokenStorage implements ActiveTokenStorageInterface
{
    private Redis $redisClient;
    private string $keyPrefix;

    /**
     * @param Redis $redisClient The configured Redis client instance.
     * @param string $keyPrefix The prefix for active token keys (e.g., "auth:active:").
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
    public function markAsActive(string $tokenId, int $ttl, string|int|null $userId = null): void
    {
        if ($ttl <= 0) {
            throw StorageException::forSave($this->keyPrefix . $tokenId, 'TTL must be greater than zero.');
        }
        $key = $this->keyPrefix . $tokenId;
        $value = ($userId !== null) ? (string) $userId : '1';

        try {
            $result = $this->redisClient->setex($key, $ttl, $value);

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
    public function isActive(string $tokenId): bool
    {
        $key = $this->keyPrefix . $tokenId;

        try {
            return (int) $this->redisClient->exists($key) > 0;
        } catch (Throwable $e) {
            throw StorageException::forRead($key, $e->getMessage(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(string $tokenId): void
    {
        $key = $this->keyPrefix . $tokenId;

        try {
            $this->redisClient->del($key);
        } catch (Throwable $e) {
            throw StorageException::forDelete($key, $e->getMessage(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findUserIdByTokenId(string $tokenId): string|int|null
    {
        $key = $this->keyPrefix . $tokenId;

        try {
            $value = $this->redisClient->get($key);

            if ($value === false || $value === null || $value === '1') {
                return null;
            }

            return is_numeric($value) ? (int) $value : $value;
        } catch (Throwable $e) {
            throw StorageException::forRead($key, $e->getMessage(), $e);
        }
    }
}
