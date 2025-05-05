<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use function dirname;

final class KeronDigitalAuthRedisStorageBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
