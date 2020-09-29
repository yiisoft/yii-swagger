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
        $serviceWithDebug = $service->withDebug();

        $this->assertInstanceOf(SwaggerServiceInterface::class, $service);
        $this->assertInstanceOf(SwaggerServiceInterface::class, $serviceWithDebug);

        $this->assertNotSame($service, $serviceWithDebug);
    }

    public function testView()
    {
        $service = $this->createService();
        $serviceWithViewPath = $service->withViewPath('/');
        $serviceWithViewName = $service->withViewName('test');

        $this->assertInstanceOf(SwaggerServiceInterface::class, $serviceWithViewPath);
        $this->assertInstanceOf(SwaggerServiceInterface::class, $serviceWithViewName);

        $this->assertNotEquals($service->getViewPath(), $serviceWithViewPath->getViewPath());
        $this->assertNotEquals($service->getViewName(), $serviceWithViewName->getViewName());

        $this->assertEquals('/', $serviceWithViewPath->getViewPath());
        $this->assertEquals('test', $serviceWithViewName->getViewName());
    }

    public function testFetch()
    {
        $service = $this->createService();
        $openApi = $service->fetch([__DIR__ . '/data']);
        $this->assertInstanceOf(OpenApi::class, $openApi);
    }
}
