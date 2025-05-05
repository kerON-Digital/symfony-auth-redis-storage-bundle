<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Domain\Contract;

use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;

/**
 * Interface for managing markers of active tokens.
 */
interface ActiveTokenStorageInterface
{
    /**
     * Marks a token as active for its entire TTL.
     * If the token ID already exists, its TTL should be updated.
     *
     * @param string $tokenId The unique identifier (jti, rt_id) of the token.
     * @param int $ttl The full time-to-live of the token in seconds (must be > 0).
     * @param string|int|null $userId (Optional) The associated user ID, if needed for storage/lookup.
     * @throws StorageException If marking the token as active fails or TTL is invalid.
     */
    public function markAsActive(string $tokenId, int $ttl, string|int|null $userId = null): void;

    /**
     * Checks if a token is currently marked as active (exists and has not expired).
     *
     * @param string $tokenId The unique identifier (jti, rt_id) of the token.
     * @return bool True if the token is marked as active, false otherwise.
     * @throws StorageException If checking the active status fails.
     */
    public function isActive(string $tokenId): bool;

    /**
     * Explicitly removes a token from the active marker storage.
     * Used for explicit logout or when blacklisting.
     *
     * @param string $tokenId The unique identifier (jti, rt_id) of the token.
     * @throws StorageException If deleting the active marker fails.
     */
    public function revoke(string $tokenId): void;

    /**
     * Finds the User ID associated with an active token ID, if it was stored.
     *
     * @param string $tokenId The unique identifier (jti, rt_id) of the token.
     * @return string|int|null The associated User ID, or null if not found or not stored.
     * @throws StorageException If reading fails.
     */
    public function findUserIdByTokenId(string $tokenId): string|int|null;
}
