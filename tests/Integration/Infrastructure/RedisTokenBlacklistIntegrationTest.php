<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Tests\Integration\Infrastructure;

use KeronDigital\AuthRedisStorageBundle\Domain\Contract\TokenBlacklistInterface;

/**
 * Integration tests for the RedisTokenBlacklist service using a real Redis connection.
 * @covers \KeronDigital\AuthRedisStorageBundle\Infrastructure\Persistence\RedisTokenBlacklist
 * @group Integration
 */
final class RedisTokenBlacklistIntegrationTest extends RedisIntegrationTestCase
{
    private TokenBlacklistInterface $blacklistService;
    private string $testPrefix = 'auth:bl:'; // Default prefix used in TestKernel

    protected function setUp(): void
    {
        parent::setUp();
        $service = static::getContainer()->get(TokenBlacklistInterface::class);

        if (! $service instanceof TokenBlacklistInterface) {
            $this->fail('Could not retrieve TokenBlacklistInterface service from container.');
        }
        $this->blacklistService = $service;
    }

    public function testBlacklistAddsKeyWithCorrectTtl(): void
    {
        $tokenId = 'integ_token_1';
        $ttl = 60;
        $expectedKey = $this->testPrefix . $tokenId;

        $this->assertSame(0, self::$redisClient->exists($expectedKey), "Key $expectedKey should not exist before blacklisting.");


        // Act
        $this->blacklistService->blacklist($tokenId, $ttl);

        // Assert
        $this->assertTrue(self::$redisClient->exists($expectedKey) > 0, "Key $expectedKey should exist after blacklisting.");
        $actualTtl = self::$redisClient->ttl($expectedKey);
        $this->assertGreaterThan(0, $actualTtl, "Key $expectedKey should have a positive TTL.");
        $this->assertLessThanOrEqual($ttl, $actualTtl, "Key $expectedKey TTL should be less than or equal to the requested TTL.");
        $this->assertSame('1', self::$redisClient->get($expectedKey));
    }

    public function testBlacklistDoesNothingForZeroTtl(): void
    {
        $tokenId = 'integ_token_zero';
        $ttl = 0;
        $expectedKey = $this->testPrefix . $tokenId;

        // Act
        $this->blacklistService->blacklist($tokenId, $ttl);

        // Assert
        $this->assertSame(0, self::$redisClient->exists($expectedKey));
    }

    public function testBlacklistDoesNothingForNegativeTtl(): void
    {
        $tokenId = 'integ_token_neg';
        $ttl = -100;
        $expectedKey = $this->testPrefix . $tokenId;

        // Act
        $this->blacklistService->blacklist($tokenId, $ttl);

        // Assert
        $this->assertSame(0, self::$redisClient->exists($expectedKey));
    }

    public function testIsBlacklistedReturnsTrueForExistingKey(): void
    {
        $tokenId = 'integ_token_exists';
        $ttl = 120;
        $key = $this->testPrefix . $tokenId;

        // Arrange
        self::$redisClient->setex($key, $ttl, '1');

        // Act & Assert
        $this->assertTrue($this->blacklistService->isBlacklisted($tokenId));
    }

    public function testIsBlacklistedReturnsFalseForNonExistingKey(): void
    {
        $tokenId = 'integ_token_missing';

        // Act & Assert
        $this->assertFalse($this->blacklistService->isBlacklisted($tokenId));
    }

    public function testIsBlacklistedReturnsFalseForExpiredKey(): void
    {
        $tokenId = 'integ_token_expired';
        $ttl = 1;
        $key = $this->testPrefix . $tokenId;

        // Arrange
        self::$redisClient->setex($key, $ttl, '1');
        sleep($ttl + 1);

        // Act & Assert
        $this->assertFalse($this->blacklistService->isBlacklisted($tokenId), 'Expired key should not be considered blacklisted.');
        $this->assertSame(0, self::$redisClient->exists($key), 'Redis key should not exist after expiry.');
    }
}
