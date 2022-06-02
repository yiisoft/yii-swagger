<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Annotation paths cannot be empty array.');

        $this
            ->createService()
            ->fetch([]);
    }

    public function testImmutability(): void
    {
        $service = $this->createService();

        $this->assertNotSame($service, $service->withOptions([]));
    }

    private function createService(): SwaggerService
    {
        return new SwaggerService(new Aliases());
    }
}
