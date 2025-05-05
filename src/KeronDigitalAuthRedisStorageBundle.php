<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle;

use KeronDigital\AuthRedisStorageBundle\Infrastructure\DependencyInjection\KeronDigitalAuthRedisStorageExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class KeronDigitalAuthRedisStorageBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new KeronDigitalAuthRedisStorageExtension();
    }
}
