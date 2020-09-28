<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Di\Container;
use Yiisoft\Swagger\Interfaces\SwaggerServiceInterface;
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

    private function createService(): SwaggerServiceInterface
    {
        $container = $this->createContainer();
        return new SwaggerService($container);
    }

    public function testCreateService(): void
    {
        $service = $this->createService();

        $this->assertInstanceOf(SwaggerServiceInterface::class, $service);
        $this->assertFalse($service->isDebug());

        $serviceWithDebug = $service->withDebug();
        $this->assertInstanceOf(SwaggerServiceInterface::class, $serviceWithDebug);
        $this->assertTrue($serviceWithDebug->isDebug());
    }

    public function testView()
    {
        $service = $this->createService();

        $this->assertIsString($service->getViewPath());
        $this->assertIsString($service->getViewName());

        $this->assertInstanceOf(SwaggerServiceInterface::class, $service->withViewPath('/'));
        $this->assertInstanceOf(SwaggerServiceInterface::class, $service->withViewName('test'));

        $this->assertEquals('/', $service->withViewPath('/')->getViewPath());
        $this->assertEquals('test', $service->withViewName('test')->getViewName());
    }

    public function testFetch()
    {
        $service = $this->createService();
        $openApi = $service->fetch([__DIR__ . '/data']);
        $this->assertInstanceOf(OpenApi::class, $openApi);
    }
}
