# Keron Digital Auth Redis Storage Bundle

[![Latest Stable Version](https://poser.pugx.org/keron-digital/auth-redis-storage-bundle/v)](https://packagist.org/packages/keron-digital/auth-redis-storage-bundle)
[![License](https://poser.pugx.org/keron-digital/auth-redis-storage-bundle/license)](https://packagist.org/packages/keron-digital/auth-redis-storage-bundle)
Provides Redis-based services for token blacklisting and active token tracking in Symfony applications, using unique token identifiers (e.g., JTI, UUID).

## Quick Start

### 1. Installation

Install the bundle using Composer:

```bash
composer require keron-digital/auth-redis-storage-bundle
```

Enable the bundle in your `config/bundles.php`:

```php
<?php
// config/bundles.php

return [
    // ... other bundles
    KeronDigital\AuthRedisStorageBundle\KeronDigitalAuthRedisStorageBundle::class => ['all' => true],
];
```

### 2. Configuration

Ensure you have a Redis client service already configured in your Symfony application. Then, create the bundle's configuration file and specify your client service ID:

```yaml
# config/packages/keron_digital_auth_redis_storage.yaml
keron_digital_auth_redis_storage:
    # REQUIRED: Point this to your application's Redis service ID
    # The client service must provide e.g. \Redis, \Predis\ClientInterface, or a PSR Cache Pool using Redis
    redis_client_service_id: 'snc_redis.default' # Example ID, change as needed
```

The bundle uses default key prefixes (`auth:bl:` for blacklist, `auth:active:` for active tokens).

*See [docs/configuration.md](docs/configuration.md) for details on customizing prefixes and other options.*

### 3. Basic Usage

Inject the interfaces into your services where needed:

* `KeronDigital\AuthRedisStorageBundle\Domain\Contract\TokenBlacklistInterface`
* `KeronDigital\AuthRedisStorageBundle\Domain\Contract\ActiveTokenStorageInterface`

**Example: Checking if a token is blacklisted**

```php
<?php

namespace App\Security; // Your application's namespace

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\TokenBlacklistInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TokenValidator
{
    public function __construct(private readonly TokenBlacklistInterface $blacklist) {}

    /**
     * @throws AuthenticationException
     */
    public function validateTokenId(string $tokenId): void
    {
        if ($this->blacklist->isBlacklisted($tokenId)) {
            // Consider logging this attempt
            throw new AuthenticationException('Token is blacklisted.');
        }
        // ... other validation ...
    }
}
```

*See [docs/usage.md](docs/usage.md) for more examples on blacklisting, marking tokens active, checking active status, and revoking.*

## Documentation

For detailed information, please refer to the `docs/` directory (you will need to create this):

* `docs/configuration.md`: Detailed configuration options.
* `docs/usage.md`: Comprehensive usage examples.
* `docs/testing.md`: Information on running the bundle's tests (for contributors).

## License

This bundle is released under the MIT License. See the LICENSE file for details (you should create this file too).