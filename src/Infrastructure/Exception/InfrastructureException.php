<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Infrastructure\Exception;

use RuntimeException;

/**
 * Base exception for infrastructure-related errors.
 */
abstract class InfrastructureException extends RuntimeException
{
}
