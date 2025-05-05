# Configuration Reference

This document details all available configuration options for the `KeronDigitalAuthRedisStorageBundle`.

Configuration is typically done in a file like `config/packages/keron_digital_auth_redis_storage.yaml` within your Symfony application.

## Main Configuration Key

All bundle configuration options are nested under the `keron_digital_auth_redis_storage` key:

```yaml
# config/packages/keron_digital_auth_redis_storage.yaml
keron_digital_auth_redis_storage:
    # Bundle configuration options go here
    ...
```

## Available Options

### `redis_client_service_id`

* **Type:** `string`
* **Required:** Yes
* **Description:** Specifies the service ID of the Redis client service that is already configured within your Symfony application. This bundle uses this client to interact with your Redis server. The provided service should typically implement `\Redis`, `\RedisArray`, `\RedisCluster`, `\Predis\ClientInterface`, or be a PSR-6/PSR-16 cache pool instance configured with a Redis adapter (like `symfony/cache`).

    ```yaml
    keron_digital_auth_redis_storage:
        redis_client_service_id: 'snc_redis.default' # Example using SncRedisBundle
        # or
        # redis_client_service_id: 'cache.app.redis' # Example using Symfony Cache Pool
    ```

### `key_prefix`

* **Type:** `string`
* **Required:** No
* **Default:** `'auth:'`
* **Description:** A general prefix that will be prepended to all Redis keys managed by this bundle. This is useful for namespacing the bundle's keys if you are using a shared Redis instance for multiple purposes or applications. The final key will be `key_prefix` + `service_suffix` + `token_id`.

    ```yaml
    keron_digital_auth_redis_storage:
        # ... redis_client_service_id
        key_prefix: 'my_app_auth:'
    ```

### `blacklist`

* **Type:** `array`
* **Required:** No
* **Description:** Configures the token blacklist service.
    * **`key_suffix`**:
        * **Type:** `string`
        * **Required:** No
        * **Default:** `'bl:'`
        * **Description:** A specific suffix used for keys storing blacklisted token IDs. It is appended after the general `key_prefix`. Example resulting key: `auth:bl:<token_id>`.

    ```yaml
    keron_digital_auth_redis_storage:
        # ... other config
        blacklist:
            key_suffix: 'token_blacklist:' # Example custom suffix
    ```

### `active_token_storage`

* **Type:** `array`
* **Required:** No
* **Description:** Configures the active token storage service.
    * **`key_suffix`**:
        * **Type:** `string`
        * **Required:** No
        * **Default:** `'active:'`
        * **Description:** A specific suffix used for keys marking tokens as active. It is appended after the general `key_prefix`. Example resulting key: `auth:active:<token_id>`.

    ```yaml
    keron_digital_auth_redis_storage:
        # ... other config
        active_token_storage:
            key_suffix: 'current_tokens:' # Example custom suffix
    ```

## Full Configuration Example

```yaml
# config/packages/keron_digital_auth_redis_storage.yaml
keron_digital_auth_redis_storage:
    # Use the Redis client service named 'cache.redis.auth' defined elsewhere in the app
    redis_client_service_id: 'cache.redis.auth'

    # Prefix all keys with 'my_secure_app:auth:'
    key_prefix: 'my_secure_app:auth:'

    # Configure blacklist keys like 'my_secure_app:auth:invalidated:'
    blacklist:
        key_suffix: 'invalidated:'

    # Configure active token keys like 'my_secure_app:auth:session:'
    active_token_storage:
        key_suffix: 'session:'
```