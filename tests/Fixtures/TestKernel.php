<?php

declare(strict_types=1);

namespace KeronDigital\AuthRedisStorageBundle\Tests\Fixtures;

use KeronDigital\AuthRedisStorageBundle\Infrastructure\DependencyInjection\KeronDigitalAuthRedisStorageExtension;
use KeronDigital\AuthRedisStorageBundle\KeronDigitalAuthRedisStorageBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * A minimal kernel for integration testing the bundle.
 * Explicitly loads configuration files in a specific order.
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

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader): void
    {
        $confDir = $this->getConfDir();

        $container->import($confDir.'/{packages}/framework.yaml');
        $container->import($confDir.'/{packages}/keron_digital_auth_redis_storage.yaml');
        $container->import($confDir.'/{services}.yaml');

        $container->parameters()->set('kernel.secret', 'test_secret');
        $container->parameters()->set('kernel.test', true);
    }

    /**
     * Define the directory containing the test configuration files.
     */
    public function getConfDir(): string
    {
        return $this->getProjectDir().'/tests/Fixtures/config';
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
