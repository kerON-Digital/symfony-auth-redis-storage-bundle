<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Tests\Unit\Infrastructure\Persistence;

use KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception\StorageException;
use KeronDigital\AuthRedisStorageBundle\Infrastructure\Persistence\RedisActiveTokenStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Redis;
use RedisException;

/**
 * Unit tests for the RedisActiveTokenStorage service.
 * @covers \KeronDigital\AuthRedisStorageBundle\Infrastructure\Persistence\RedisActiveTokenStorage
 */
final class RedisActiveTokenStorageTest extends TestCase
{
    private MockObject|Redis $redisClientMock; // Adjust type hint if needed
    private RedisActiveTokenStorage $service;
    private string $prefix = 'test_active:';

    protected function setUp(): void
    {
        $this->redisClientMock = $this->createMock(Redis::class); // Adjust class if needed
        $this->service = new RedisActiveTokenStorage($this->redisClientMock, $this->prefix);
    }

    //--- markAsActive Tests ---

    public function testMarkAsActiveSavesWithUserIdWhenProvided(): void
    {
        $tokenId = 'token1';
        $ttl = 600;
        $userId = 'user-abc';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $ttl, $userId) // Expecting userId as value
            ->willReturn(true);

        $this->service->markAsActive($tokenId, $ttl, $userId);
    }

    public function testMarkAsActiveSavesWithDefaultMarkerWhenUserIdIsNull(): void
    {
        $tokenId = 'token2';
        $ttl = 700;
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $ttl, '1') // Expecting '1' as value
            ->willReturn(true);

        $this->service->markAsActive($tokenId, $ttl, null); // Pass null for userId
    }

    /**
     * @dataProvider invalidTtlProvider
     */
    public function testMarkAsActiveThrowsExceptionForInvalidTtl(int $invalidTtl): void
    {
        $tokenId = 'token3';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock->expects($this->never())->method('setex'); // Should not be called

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to save key "%s": TTL must be greater than zero.', $expectedKey));

        $this->service->markAsActive($tokenId, $invalidTtl, 'user-123');
    }

    /**
     * Provides invalid TTL values for testing.
     *
     * @return array<string, array{int}> Data for test cases: [description => [invalidTtl]]
     */
    public static function invalidTtlProvider(): array
    {
        return [
            'zero ttl' => [0],
            'negative ttl' => [-300],
        ];
    }

    public function testMarkAsActiveThrowsStorageExceptionOnRedisSetexFailure(): void
    {
        $tokenId = 'token4';
        $ttl = 100;
        $userId = 'user-xyz';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $ttl, $userId)
            ->willThrowException(new RedisException('Connection failed'));

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to save key "%s": Connection failed', $expectedKey));

        $this->service->markAsActive($tokenId, $ttl, $userId);
    }

    public function testMarkAsActiveThrowsStorageExceptionOnRedisSetexReturnsFalse(): void
    {
        $tokenId = 'token5';
        $ttl = 100;
        $userId = 'user-xyz';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $ttl, $userId)
            ->willReturn(false);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to save key "%s": SETEX command failed or returned false.', $expectedKey));

        $this->service->markAsActive($tokenId, $ttl, $userId);
    }

    //--- isActive Tests ---

    public function testIsActiveReturnsTrueWhenKeyExists(): void
    {
        $tokenId = 'token6';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('exists')
            ->with($expectedKey)
            ->willReturn(1);

        $this->assertTrue($this->service->isActive($tokenId));
    }

    public function testIsActiveReturnsFalseWhenKeyDoesNotExist(): void
    {
        $tokenId = 'token7';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('exists')
            ->with($expectedKey)
            ->willReturn(0);

        $this->assertFalse($this->service->isActive($tokenId));
    }

    public function testIsActiveThrowsStorageExceptionOnRedisExistsFailure(): void
    {
        $tokenId = 'token8';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('exists')
            ->with($expectedKey)
            ->willThrowException(new RedisException('Read error'));

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to read key "%s": Read error', $expectedKey));

        $this->service->isActive($tokenId);
    }

    //--- revoke Tests ---

    public function testRevokeCallsDelOnRedisClient(): void
    {
        $tokenId = 'token9';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('del')
            ->with($expectedKey); // del in phpredis takes string or array

        $this->service->revoke($tokenId);
    }

    public function testRevokeThrowsStorageExceptionOnRedisDelFailure(): void
    {
        $tokenId = 'token10';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('del')
            ->with($expectedKey)
            ->willThrowException(new RedisException('Write error'));

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to delete key "%s": Write error', $expectedKey));

        $this->service->revoke($tokenId);
    }

    //--- findUserIdByTokenId Tests ---

    public function testFindUserIdByTokenIdReturnsStringUserIdWhenStored(): void
    {
        $tokenId = 'token11';
        $userId = 'user-the-id';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock->expects($this->once())->method('get')->with($expectedKey)->willReturn($userId);
        $this->assertSame($userId, $this->service->findUserIdByTokenId($tokenId));
    }

    public function testFindUserIdByTokenIdReturnsIntUserIdWhenStored(): void
    {
        $tokenId = 'token12';
        $userId = '98765'; // Stored as string in Redis
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock->expects($this->once())->method('get')->with($expectedKey)->willReturn($userId);
        $this->assertSame(98765, $this->service->findUserIdByTokenId($tokenId)); // Expect int
    }

    public function testFindUserIdByTokenIdReturnsNullWhenDefaultMarkerStored(): void
    {
        $tokenId = 'token13';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock->expects($this->once())->method('get')->with($expectedKey)->willReturn('1'); // Default marker
        $this->assertNull($this->service->findUserIdByTokenId($tokenId));
    }

    public function testFindUserIdByTokenIdReturnsNullWhenKeyNotFound(): void
    {
        $tokenId = 'token14';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock->expects($this->once())->method('get')->with($expectedKey)->willReturn(false); // Redis returns false if not found
        $this->assertNull($this->service->findUserIdByTokenId($tokenId));
    }

    public function testFindUserIdByTokenIdThrowsStorageExceptionOnRedisGetFailure(): void
    {
        $tokenId = 'token15';
        $expectedKey = $this->prefix . $tokenId;

        $this->redisClientMock
            ->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willThrowException(new RedisException('Connection error'));

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(sprintf('Failed to read key "%s": Connection error', $expectedKey));

        $this->service->findUserIdByTokenId($tokenId);
    }
}
