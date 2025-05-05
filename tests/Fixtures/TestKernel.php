<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Tests\Fixtures;

use KeronDigital\AuthRedisStorageBundle\KeronDigitalAuthRedisStorageBundle;
use KeronDigital\AuthRedisStorageBundle\Infrastructure\DependencyInjection\KeronDigitalAuthRedisStorageExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * A minimal kernel for integration testing the bundle, loading config from files.
 */
class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new KeronDigitalAuthRedisStorageBundle();
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->registerExtension(new KeronDigitalAuthRedisStorageExtension());
    }

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $configDir = $this->getProjectDir().'/tests/Fixtures/config';

        $container->import($configDir.'/{packages}/framework.yaml');
        $container->import($configDir.'/{services}.yaml');
        $container->import($configDir.'/{packages}/keron_digital_auth_redis_storage.yaml');


        $container->extension('framework', [
            'test' => true,
            'secret' => 'test_secret',
            'router' => ['utf8' => true],
            'cache' => ['app' => 'cache.adapter.filesystem'],
            'session' => ['storage_factory_id' => 'session.storage.factory.mock_file'],
        ]);
    }

    /**
     * Configure routes. Needed even if empty for the kernel to boot.
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // No routes needed for these tests
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__, 2);
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/log';
    }
}