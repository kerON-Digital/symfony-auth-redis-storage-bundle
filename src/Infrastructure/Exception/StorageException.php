<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception;

use Throwable;

/**
 * Base exception for storage-related errors in the Infrastructure layer.
 */
class StorageException extends InfrastructureException
{
    /**
     * Creates an exception for a failed save operation.
     */
    public static function forSave(string $key, string $reason, ?Throwable $previous = null): self
    {
        $message = sprintf('Failed to save key "%s": %s', $key, $reason);

        return new self($message, 0, $previous);
    }

    /**
     * Creates an exception for a failed read operation.
     */
    public static function forRead(string $key, string $reason, ?Throwable $previous = null): self
    {
        $message = sprintf('Failed to read key "%s": %s', $key, $reason);

        return new self($message, 0, $previous);
    }

    /**
     * Creates an exception for a failed delete operation.
     */
    public static function forDelete(string $key, string $reason, ?Throwable $previous = null): self
    {
        $message = sprintf('Failed to delete key "%s": %s', $key, $reason);

        return new self($message, 0, $previous);
    }
}
