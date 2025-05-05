# Usage Guide

This guide provides examples on how to use the services provided by the `KeronDigitalAuthRedisStorageBundle` within your Symfony application.

The bundle exposes two main services through their domain interfaces:

* `KeronDigital\AuthRedisStorageBundle\Domain\Contract\TokenBlacklistInterface`: For managing blacklisted token identifiers.
* `KeronDigital\AuthRedisStorageBundle\Domain\Contract\ActiveTokenStorageInterface`: For managing active token identifiers.

Inject these interfaces into your services, event listeners, controllers, or authenticators using Symfony's dependency injection.

## Examples

### 1. Marking a Token as Active

After generating a token (e.g., an access token JWT or a refresh token), you should mark its unique identifier (`jti` or a generated UUID) as active. This allows you to later verify if the token *should* be considered valid (i.e., it was issued and hasn't been explicitly revoked).

```php
<?php
// Example: Service that generates tokens and marks them active
namespace App\Service;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\ActiveTokenStorageInterface;
use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;
use Psr\Log\LoggerInterface;

class TokenManager
{
    public function __construct(
        private readonly ActiveTokenStorageInterface $activeTokenStorage,
        private readonly LoggerInterface $logger
        // ... inject other services like JWT creator or refresh token generator
    ) {}

    public function issueAndMarkActive(string $userId, int $tokenTtl): ?string
    {
        // 1. Generate your token (e.g., JWT with a unique JTI)
        $tokenId = $this->generateUniqueTokenId(); // Implement this (e.g., Uuid::v4()->toRfc4122())
        // $jwt = $this->jwtManager->create([... 'jti' => $tokenId ...], $tokenTtl); // Example

        if (!$tokenId) {
            return null;
        }

        // 2. Mark the token ID as active in Redis via the bundle's service
        try {
            // Store the user ID along with the active marker
            $this->activeTokenStorage->markAsActive($tokenId, $tokenTtl, $userId);
            $this->logger->info('Token marked active.', ['tokenId' => $tokenId, 'userId' => $userId, 'ttl' => $tokenTtl]);
            // Return the generated token string or ID
            return $tokenId; // Or the full JWT string, etc.
        } catch (StorageException $e) {
            $this->logger->error('Failed to mark token active in Redis.', [
                'tokenId' => $tokenId,
                'userId' => $userId,
                'exception' => $e->getMessage(),
            ]);
            // Decide how to handle the error in your application
            return null;
        }
    }

    private function generateUniqueTokenId(): string {
        // Example using symfony/uid
        // Ensure you have `composer require symfony/uid`
        // return \Symfony\Component\Uid\Uuid::v4()->toRfc4122();

        // Or use any other method to get a unique ID
        return bin2hex(random_bytes(16));
    }
}
```

### 2. Checking if a Token is Active

In your security layer (e.g., a custom authenticator or an event listener after authentication), you might want to check if the presented token's ID is still marked as active. This prevents the use of tokens that might have been explicitly revoked (e.g., during logout) even if they haven't expired yet.

```php
<?php
// Example: Part of a custom JWT Authenticator or Event Listener
namespace App\Security;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\ActiveTokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
// ... other necessary classes

class TokenAuthenticator // ... or EventListener
{
    public function __construct(
        private readonly ActiveTokenStorageInterface $activeTokenStorage
        // ... other dependencies
    ) {}

    public function validateToken(string $tokenId): void // Or pass the decoded JWT payload
    {
        // ... other validation (signature, expiry) done by LexikJWT or similar ...

        // Check if the token ID is still marked as active
        if (!$this->activeTokenStorage->isActive($tokenId)) {
            // Token might have expired (and Redis cleaned it up) OR it was explicitly revoked
            throw new AuthenticationException('Token is no longer active or has been revoked.');
        }

        // Token is active, proceed...
    }
}
```

### 3. Blacklisting a Token

When a token needs to be invalidated *before* its natural expiration (e.g., user logs out, password change, suspected compromise), add its identifier to the blacklist.

```php
<?php
// Example: Service handling user logout
namespace App\Service;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\TokenBlacklistInterface;
use KeronDigital\AuthRedisStorageBundle\Domain\Contract\ActiveTokenStorageInterface;
use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;
use Psr\Log\LoggerInterface;

class LogoutHandler
{
    public function __construct(
        private readonly TokenBlacklistInterface $blacklistStorage,
        private readonly ActiveTokenStorageInterface $activeTokenStorage,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Invalidates a specific token ID.
     *
     * @param string $tokenId The JTI or rtId to invalidate.
     * @param int $expiresAtTimestamp The original 'exp' timestamp of the token.
     */
    public function invalidateToken(string $tokenId, int $expiresAtTimestamp): void
    {
        $remainingTtl = $expiresAtTimestamp - time();

        try {
            // 1. Remove it from the active list (important!)
            $this->activeTokenStorage->revoke($tokenId);

            // 2. Add it to the blacklist if it hasn't already expired
            if ($remainingTtl > 0) {
                $this->blacklistStorage->blacklist($tokenId, $remainingTtl);
                $this->logger->info('Token revoked and blacklisted.', ['tokenId' => $tokenId]);
            } else {
                $this->logger->info('Token revoked (already expired, not blacklisted).', ['tokenId' => $tokenId]);
            }

        } catch (StorageException $e) {
            $this->logger->error('Failed to invalidate token.', [
                'tokenId' => $tokenId,
                'exception' => $e
            ]);
            // Decide how to handle the error
        }
    }
}
```

### 4. Checking if a Token is Blacklisted

Similar to checking if a token is active, your security layer should also check if the token's ID is on the blacklist.

```php
<?php
// Example: Part of a custom JWT Authenticator or Event Listener
namespace App\Security;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\TokenBlacklistInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
// ... other necessary classes

class TokenAuthenticator // ... or EventListener
{
    public function __construct(
        private readonly TokenBlacklistInterface $blacklistStorage
        // ... other dependencies
    ) {}

    public function validateToken(string $tokenId): void // Or pass the decoded JWT payload
    {
        // ... other validation ...

        // Check if the token ID is blacklisted
        if ($this->blacklistStorage->isBlacklisted($tokenId)) {
            throw new AuthenticationException('Token has been blacklisted.');
        }

        // Token is not blacklisted, proceed...
    }
}
```

**Recommendation:** Perform both the `isActive` check (Example 2) and the `isBlacklisted` check (Example 4) for robust validation.

### 5. Finding User ID from Active Token

If you stored the User ID when marking a token as active (using the third argument of `markAsActive`), you can retrieve it later using the token ID.

```php
<?php
// Example: Service needing the user ID associated with an active token
namespace App\Service;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\ActiveTokenStorageInterface;
use Psr\Log\LoggerInterface;

class UserActivityService
{
    public function __construct(
        private readonly ActiveTokenStorageInterface $activeTokenStorage,
        private readonly LoggerInterface $logger
    ) {}

    public function getUserIdForToken(string $tokenId): string|int|null
    {
        try {
            $userId = $this->activeTokenStorage->findUserIdByTokenId($tokenId);
            if ($userId === null) {
                $this->logger->info('No user ID found or token not active.', ['tokenId' => $tokenId]);
            }
            return $userId;
        } catch (\KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException $e) {
             $this->logger->error('Failed to find user ID by token ID.', [
                'tokenId' => $tokenId,
                'exception' => $e
            ]);
            return null;
        }
    }
}
```

These examples cover the primary use cases for the services provided by your bundle. Remember to handle potential `StorageException` errors appropriately within your application logic.