<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests;

use InvalidArgumentException;
use OpenApi\Generator;
use OpenApi\Pipeline;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Log\SimpleLogger;
use Yiisoft\Yii\Swagger\Service\SwaggerService;
use Yiisoft\Yii\Swagger\Tests\Support\GeneratorStub;
use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use RuntimeException;

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

    public function testImmutability(): void
    {
        $service = $this->createService();

        $this->assertNotSame($service, $service->withOptions([]));
    }

    public function testSwaggerServiceEmptyArrayFetch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source paths cannot be empty array.');

        $this
            ->createService()
            ->fetch([]);
    }

    public function testFetchForwardsAnalysisAndValidateFalse(): void
    {
        $analysis = new Analysis([], new Context());
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService(new Aliases(), $generator);

        $service = $service->withOptions([
            'analysis' => $analysis,
            'validate' => false,
        ]);

        $service->fetch([__DIR__]);

        $this->assertSame($analysis, $generator->receivedAnalysis);
        $this->assertFalse($generator->receivedValidate);
    }

    public function testFetchResolvesAliasesInPaths(): void
    {
        $aliases = new Aliases(['@root' => __DIR__]);
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService($aliases, $generator);

        $service->fetch(['@root/Support']);

        $this->assertNotEmpty($generator->receivedDirectories);
        $this->assertSame(__DIR__ . '/Support', $generator->receivedDirectories[0]);
    }

    public function testFetchThrowsWhenGeneratorReturnsNull(): void
    {
        $generator = new Generator(new SimpleLogger());
        $generator->setProcessorPipeline(new Pipeline());
        $service = new SwaggerService(new Aliases(), $generator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'No OpenApi target set. Run the "OpenApi\\Processors\\MergeIntoOpenApi" processor before "Yiisoft\\Yii\\Swagger\\Service\\SwaggerService::fetch()".',
        );

        $service->fetch([__DIR__]);
    }

    private function createService(): SwaggerService
    {
        return new SwaggerService(new Aliases());
    }
}
