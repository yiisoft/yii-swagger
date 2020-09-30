<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Di\Container;
use Yiisoft\Swagger\Service\SwaggerService;

final class SwaggerServiceTest extends TestCase
{
    public function createContainer(): ContainerInterface
    {
        $definitions = [
            Aliases::class => new Aliases(),
            CacheInterface::class => new ArrayCache()
        ];

        return new Container($definitions);
    }

    private function createService(): SwaggerService
    {
        $container = $this->createContainer();
        return new SwaggerService($container);
    }

    public function testCreateService(): void
    {
        $service = $this->createService();
        $cachedService = $service->withCache();

        $this->assertNotSame($service, $cachedService);
    }

    public function testView():void
    {
        $service = $this->createService();
        $serviceWithViewPath = $service->withViewPath('/');
        $serviceWithViewName = $service->withViewName('test');

        $this->assertDirectoryExists($service->getViewPath());
        $this->assertFileExists($service->getViewPath() .'/'. $service->getViewName() . '.php');

        $this->assertNotEquals($service->getViewPath(), $serviceWithViewPath->getViewPath());
        $this->assertNotEquals($service->getViewName(), $serviceWithViewName->getViewName());

        $this->assertEquals('/', $serviceWithViewPath->getViewPath());
        $this->assertEquals('test', $serviceWithViewName->getViewName());
    }
}
