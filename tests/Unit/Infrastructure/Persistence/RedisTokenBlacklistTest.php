<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Tests\Unit\Infrastructure\Persistence;

use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;
use KeronDigital\AuthRedisStorageBundle\Infrastructure\Persistence\RedisTokenBlacklist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Redis;
use RedisException;

/**
 * @covers \KeronDigital\AuthRedisStorageBundle\Infrastructure\Persistence\RedisTokenBlacklist
 */
final class RedisTokenBlacklistTest extends TestCase
{
    private MockObject|Redis $redisClientMock;
    private RedisTokenBlacklist $service;
    private string $prefix = 'test_bl:';

    protected function setUp(): void
    {
        $this->redisClientMock = $this->createMock(Redis::class);
        $this->service = new RedisTokenBlacklist($this->redisClientMock, $this->prefix);
    }

    public function testBlacklistSavesKeyWithCorrectTtlWhenTtlIsPositive(): void
    {
        $tokenId = 'token123';
        $ttl = 300;
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $ttl, '1')
            ->willReturn(true);

        $this->service->blacklist($tokenId, $ttl);
    }

    public function testBlacklistDoesNothingWhenTtlIsZeroOrNegative(): void
    {
        $this->redisClientMock
            ->expects($this->never())
            ->method('setex');

        $this->service->blacklist('token456', 0);
        $this->service->blacklist('token789', -60);
    }

    public function testBlacklistThrowsStorageExceptionOnRedisSetexFailure(): void
    {
        $tokenId = 'fail_token';
        $ttl = 100;
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $ttl, '1')
            ->willThrowException(new RedisException('Connection refused'));

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to save key "%s": Connection refused', $expectedKey));

        $this->service->blacklist($tokenId, $ttl);
    }

    public function testBlacklistThrowsStorageExceptionOnRedisSetexReturnsFalse(): void
    {
        $tokenId = 'fail_return_token';
        $ttl = 100;
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $ttl, '1')
            ->willReturn(false);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to save key "%s": SETEX command failed or returned false.', $expectedKey));

        $this->service->blacklist($tokenId, $ttl);
    }

    public function testIsBlacklistedReturnsTrueWhenKeyExists(): void
    {
        $tokenId = 'existing_token';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('exists')
            ->with($expectedKey)
            ->willReturn(1);

        $this->assertTrue($this->service->isBlacklisted($tokenId));
    }

    public function testIsBlacklistedReturnsFalseWhenKeyDoesNotExist(): void
    {
        $tokenId = 'missing_token';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('exists')
            ->with($expectedKey)
            ->willReturn(0);

        $this->assertFalse($this->service->isBlacklisted($tokenId));
    }

    public function testIsBlacklistedThrowsStorageExceptionOnRedisExistsFailure(): void
    {
        $tokenId = 'fail_check_token';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('exists')
            ->with($expectedKey)
            ->willThrowException(new RedisException('Read error on connection'));

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to read key "%s": Read error on connection', $expectedKey));

        $this->service->isBlacklisted($tokenId);
    }
}
