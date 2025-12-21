<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests;

use InvalidArgumentException;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
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

    public function testGetViewName(): void
    {
        $service = $this->createService();

        $this->assertSame('swagger-ui', $service->getViewName());
    }

    public function testGetViewPathWithAlias(): void
    {
        $aliases = new Aliases(['@views' => '/custom/views']);
        $service = new SwaggerService($aliases);

        $this->assertStringEndsWith('/views', $service->getViewPath());
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

    public function testFetchValidateDefaultsToTrue(): void
    {
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService(new Aliases(), $generator);

        $service->fetch([__DIR__]);

        $this->assertTrue($generator->receivedValidate);
    }

    public function testFetchValidateTrueWhenExplicitlySet(): void
    {
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService(new Aliases(), $generator);

        $service = $service->withOptions(['validate' => true]);
        $service->fetch([__DIR__]);

        $this->assertTrue($generator->receivedValidate);
    }

    public function testFetchWithNullAnalysisOption(): void
    {
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService(new Aliases(), $generator);

        $service = $service->withOptions(['analysis' => null]);
        $service->fetch([__DIR__]);

        $this->assertNull($generator->receivedAnalysis);
    }

    public function testFetchWithInvalidAnalysisOptionType(): void
    {
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService(new Aliases(), $generator);

        $service = $service->withOptions(['analysis' => 'invalid']);
        $service->fetch([__DIR__]);

        $this->assertNull($generator->receivedAnalysis);
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

    public function testFetchResolvesMultiplePaths(): void
    {
        $aliases = new Aliases(['@root' => __DIR__]);
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService($aliases, $generator);

        $service->fetch(['@root/Support', __DIR__]);

        $this->assertCount(2, $generator->receivedDirectories);
        $this->assertSame(__DIR__ . '/Support', $generator->receivedDirectories[0]);
        $this->assertSame(__DIR__, $generator->receivedDirectories[1]);
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

    public function testFetchWithInternalGeneratorAndVersionOption(): void
    {
        $service = $this->createService();

        $service = $service->withOptions(['version' => '3.1.0']);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
        $this->assertSame('3.1.0', $openApi->openapi);
    }

    public function testFetchWithInternalGeneratorAndConfigOption(): void
    {
        $service = $this->createService();

        $service = $service->withOptions([
            'config' => [
                'operationId' => ['hash' => false],
            ],
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithInternalGeneratorAndInvalidConfigOption(): void
    {
        $service = $this->createService();

        // List array should be ignored (only associative arrays are valid)
        $service = $service->withOptions([
            'config' => ['value1', 'value2'],
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithInternalGeneratorAndAliasesOption(): void
    {
        $service = $this->createService();

        $service = $service->withOptions([
            'aliases' => ['OA' => 'OpenApi\\Annotations'],
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithInternalGeneratorAndNamespacesOption(): void
    {
        $service = $this->createService();

        $service = $service->withOptions([
            'namespaces' => ['OpenApi\\Annotations\\'],
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithInternalGeneratorAndAnalyserOption(): void
    {
        $analyser = new ReflectionAnalyser([
            new AttributeAnnotationFactory(),
            new DocBlockAnnotationFactory(),
        ]);

        $service = $this->createService();
        $service = $service->withOptions([
            'analyser' => $analyser,
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithInternalGeneratorAndInvalidAnalyserOption(): void
    {
        $service = $this->createService();

        // Invalid analyser should be ignored
        $service = $service->withOptions([
            'analyser' => 'invalid',
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithInternalGeneratorAndEmptyAliasesOption(): void
    {
        $service = $this->createService();

        $service = $service->withOptions([
            'aliases' => [],
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithInternalGeneratorAndEmptyNamespacesOption(): void
    {
        $service = $this->createService();

        $service = $service->withOptions([
            'namespaces' => [],
        ]);
        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testFetchWithLogger(): void
    {
        $logger = new SimpleLogger();
        $service = new SwaggerService(new Aliases(), null, $logger);

        $openApi = $service->fetch([__DIR__ . '/Support']);

        $this->assertInstanceOf(OpenApi::class, $openApi);
    }

    public function testWithOptionsPreservesImmutability(): void
    {
        $service = $this->createService();

        $service1 = $service->withOptions(['version' => '3.0.0']);
        $service2 = $service->withOptions(['version' => '3.1.0']);

        $this->assertNotSame($service, $service1);
        $this->assertNotSame($service, $service2);
        $this->assertNotSame($service1, $service2);
    }

    public function testFetchReturnsOpenApiInstance(): void
    {
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService(new Aliases(), $generator);

        $result = $service->fetch([__DIR__]);

        $this->assertInstanceOf(OpenApi::class, $result);
    }

    public function testFetchWithMultipleOptions(): void
    {
        $analysis = new Analysis([], new Context());
        $generator = new GeneratorStub(new OpenApi([]));
        $service = new SwaggerService(new Aliases(), $generator);

        $service = $service->withOptions([
            'analysis' => $analysis,
            'validate' => false,
            'version' => '3.1.0',
        ]);

        $service->fetch([__DIR__]);

        $this->assertSame($analysis, $generator->receivedAnalysis);
        $this->assertFalse($generator->receivedValidate);
    }

    private function createService(): SwaggerService
    {
        return new SwaggerService(new Aliases());
    }
}
