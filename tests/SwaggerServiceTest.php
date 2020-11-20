<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Swagger\Service\SwaggerService;

final class SwaggerServiceTest extends TestCase
{
    public function testSwaggerServiceView(): void
    {
        $service = $this->createService();
        $viewPath = $service->getViewPath();
        $viewFile = $viewPath . '/' . $service->getViewName() . '.php';

        $this->assertDirectoryExists($service->getViewPath());
        $this->assertFileExists($viewFile);
    }

    public function testSwaggerServiceEmptyArrayFetch(): void
    {
        $service = $this->createService();
        $this->expectException(\InvalidArgumentException::class);
        $service->fetch([]);
    }

    private function createService(): SwaggerService
    {
        $container = $this->createContainer();

        return new SwaggerService($container->get(Aliases::class));
    }

    private function createContainer(): ContainerInterface
    {
        $definitions = [
            Aliases::class => new Aliases(),
        ];

        return new Container($definitions);
    }
}
