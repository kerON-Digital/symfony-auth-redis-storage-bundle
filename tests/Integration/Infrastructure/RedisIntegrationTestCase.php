<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Tests\Integration\Infrastructure;

use KeronDigital\AuthRedisStorageBundle\Tests\Fixtures\TestKernel;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Redis;

/**
 * Base class for integration tests requiring a real Redis connection.
 */
abstract class RedisIntegrationTestCase extends KernelTestCase
{
    protected static ?Redis $redisClient = null;

    /**
     * Boot the test kernel before the test class runs.
     */
    public static function setUpBeforeClass(): void
    {
        // Boot the kernel to build the container
        static::bootKernel(['environment' => 'test', 'debug' => false]);

        // Get the Redis client from the test container
        $redis = static::getContainer()->get('test.redis_client');
        if (!$redis instanceof Redis) {
            throw new RuntimeException('Could not retrieve Redis client from test container.');
        }
        self::$redisClient = $redis;

        // Initial cleanup
        self::flushRedis();
    }

    /**
     * Clean Redis before each test method.
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::flushRedis();
    }

    /**
     * Clean up after the test class runs.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        if (self::$redisClient instanceof Redis) {
            // self::$redisClient->close();
        }
        self::$redisClient = null;
    }

    /**
     * Helper method to clear the current Redis database.
     */
    protected static function flushRedis(): void
    {
        if (self::$redisClient instanceof Redis) { // Adjust type check
            self::$redisClient->flushDB();
        } else {
            throw new RuntimeException('Redis client not available for flushing.');
        }
    }

    /**
     * Override getKernelClass to specify our test kernel.
     */
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}