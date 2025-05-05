<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Domain\Contract;

use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;

/**
 * Interface for managing a blacklist of token identifiers.
 */
interface TokenBlacklistInterface
{
    /**
     * Adds a token ID to the blacklist with its remaining TTL.
     * Implementations should ignore calls with non-positive $remainingTtl.
     *
     * @param string $tokenId The unique identifier (jti, rt_id) of the token.
     * @param int $remainingTtl The remaining time-to-live in seconds (> 0).
     * @throws StorageException If saving to the blacklist fails.
     */
    public function blacklist(string $tokenId, int $remainingTtl): void;

    /**
     * Checks if a token ID is currently in the blacklist.
     *
     * @param string $tokenId The unique identifier (jti, rt_id) of the token.
     * @return bool True if the token ID is currently blacklisted, false otherwise.
     * @throws StorageException If checking the blacklist fails.
     */
    public function isBlacklisted(string $tokenId): bool;
}
