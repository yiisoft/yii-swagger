<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests;

use InvalidArgumentException;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Yiisoft\Middleware\Dispatcher\MiddlewareFactory;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;
use Yiisoft\Test\Support\Log\SimpleLogger;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Swagger\Processor\RoutePath;
use Yiisoft\Yii\Swagger\Service\SwaggerService;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\ClassOperation;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\Controller;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\ExplicitAction;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\InvokableAction;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\NestedPath;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\RequestHandlerAction;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\TerminalMiddleware;

use function array_keys;
use function array_filter;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class RoutePathTest extends TestCase
{
    public function testInvokableActionUsesGroupedRoutePath(): void
    {
        $routes = $this->createRoutes(Group::create('/api')->routes(
            Group::create('/v1')->routes(
                Route::get('/items')->name('items')->action(InvokableAction::class),
            ),
        ));

        $document = $this->generate(InvokableAction::class, $routes);

        $path = (new UrlGenerator($routes))->generate('items');
        $this->assertSame('/api/v1/items', $path);
        $this->assertSame([$path], array_keys($document['paths']));
        $this->assertSame('read-item', $document['paths'][$path]['get']['operationId']);
        $this->assertSame('Item response', $document['paths'][$path]['get']['responses']['200']['description']);
    }

    public function testPathFollowsChangedRoute(): void
    {
        $original = $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/original')->action(InvokableAction::class),
        ));
        $changed = $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/changed')->action(InvokableAction::class),
        ));

        $this->assertSame(['/original'], array_keys($original['paths']));
        $this->assertSame(['/changed'], array_keys($changed['paths']));
        $this->assertSame($original['paths']['/original']['get'], $changed['paths']['/changed']['get']);
    }

    public function testControllerMatchesActionAndHttpMethod(): void
    {
        $routes = $this->createRoutes(Group::create('/content')->routes(
            Route::get('/{id}')->name('read')->action([Controller::class, 'item']),
            Route::post('/write/{id}')->name('write')->action([Controller::class, 'item']),
            Route::get('/unrelated')->action([Controller::class, 'other']),
        ));

        $document = $this->generate(Controller::class, $routes);

        $this->assertSame(['/content/{id}', '/content/write/{id}'], array_keys($document['paths']));
        $this->assertSame(['get'], array_keys($document['paths']['/content/{id}']));
        $this->assertSame(['post'], array_keys($document['paths']['/content/write/{id}']));
        $operation = $document['paths']['/content/{id}']['get'];
        $this->assertSame('id', $operation['parameters'][0]['name']);
        $this->assertSame('path', $operation['parameters'][0]['in']);
        $this->assertTrue($operation['parameters'][0]['required']);
        $this->assertSame('integer', $operation['parameters'][0]['schema']['type']);
        $this->assertSame(
            'Created item',
            $document['paths']['/content/write/{id}']['post']['responses']['201']['description'],
        );
        $this->assertSame('/content/42', (new UrlGenerator($routes))->generate('read', ['id' => 42]));
    }

    public function testRequestHandlerClassUsesHandleMethod(): void
    {
        $document = $this->generate(RequestHandlerAction::class, $this->createRoutes(
            Route::get('/handler')->action(RequestHandlerAction::class),
        ));

        $this->assertSame('Handler response', $document['paths']['/handler']['get']['responses']['200']['description']);
    }

    public function testLiteralAndEncodedSegmentsArePreserved(): void
    {
        $document = $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/v1.0/~items/a%20b')->action('\\' . InvokableAction::class),
        ));

        $this->assertSame(['/v1.0/~items/a%20b'], array_keys($document['paths']));
    }

    public function testExplicitPathIsUnchangedWithoutMatchingRoutes(): void
    {
        $document = $this->generate(ExplicitAction::class, $this->createRoutes());

        $this->assertSame(['/explicit'], array_keys($document['paths']));
    }

    public function testExplicitPathIsNotReplacedByAmbiguousUnsupportedRoutes(): void
    {
        $document = $this->generate(ExplicitAction::class, $this->createRoutes(
            Route::get('/first[/{id}]')->name('first')->action(ExplicitAction::class),
            Route::get('/second/{id:\\d+}')->name('second')->action(ExplicitAction::class),
        ));

        $this->assertSame(['/explicit'], array_keys($document['paths']));
    }

    public function testNestedExplicitPathIsUnchanged(): void
    {
        $document = $this->generate(NestedPath::class, $this->createRoutes());

        $this->assertSame('Nested response', $document['paths']['/nested']['get']['responses']['200']['description']);
    }

    public function testExistingServiceGenerationIsUnchangedWithoutProcessor(): void
    {
        $openApi = (new SwaggerService(new Aliases()))->fetch([__DIR__ . '/Support/ApiMock.php']);
        $document = json_decode($openApi->toJson(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            'Test api response',
            $document['paths']['/api/test']['get']['responses']['200']['description'],
        );
    }

    public function testMissingMatchingRouteHasActionableDiagnostic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(InvokableAction::class, $this->createRoutes());
    }

    public function testWrongHttpMethodDoesNotMatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(InvokableAction::class, $this->createRoutes(
            Route::post('/items')->action(InvokableAction::class),
        ));
    }

    public function testMultipleMatchingRoutesDoNotChooseOneSilently(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('multiple matching routes (first, second)');

        $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/second')->name('second')->action(InvokableAction::class),
            Route::get('/first')->name('first')->action(InvokableAction::class),
        ));
    }

    public function testClosureDoesNotInferEarlierMiddlewareAsAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/closure')->middleware(InvokableAction::class)->action(static fn() => null),
        ));
    }

    public function testDisabledHandlerDoesNotMatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/disabled')->action(InvokableAction::class)->disableMiddleware(InvokableAction::class),
        ));
    }

    public function testTrailingMiddlewareDoesNotExposeEarlierHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/pipeline')->middleware(InvokableAction::class)->action(TerminalMiddleware::class),
        ));
    }

    public function testMiddlewareClassStringDoesNotExposeUnusedInvokableMethod(): void
    {
        $middleware = new TerminalMiddleware();
        $factory = new MiddlewareFactory(new SimpleContainer([TerminalMiddleware::class => $middleware]));
        $this->assertSame($middleware, $factory->create(TerminalMiddleware::class));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(TerminalMiddleware::class, $this->createRoutes(
            Route::get('/middleware')->action(TerminalMiddleware::class),
        ));
    }

    public function testArrayDefinitionDoesNotExposeEarlierHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get('/pipeline')->middleware(InvokableAction::class)->action(['class' => TerminalMiddleware::class]),
        ));
    }

    public function testObjectCallableDoesNotMatchItsAnnotatedClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no matching terminal route handler');

        $this->generate(TerminalMiddleware::class, $this->createRoutes(
            Route::get('/object')->action([new TerminalMiddleware(), '__invoke']),
        ));
    }

    public function testFinalEnabledHandlerNeedNotBeTheActionDefinition(): void
    {
        $document = $this->generate(RequestHandlerAction::class, $this->createRoutes(
            Route::get('/terminal')
                ->middleware(InvokableAction::class, RequestHandlerAction::class)
                ->action(TerminalMiddleware::class)
                ->disableMiddleware(TerminalMiddleware::class),
        ));

        $this->assertSame(
            'Handler response',
            $document['paths']['/terminal']['get']['responses']['200']['description'],
        );
    }

    /**
     * @dataProvider unsupportedPatterns
     */
    public function testUnsupportedPatternsRequireExplicitPath(string $pattern): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported pattern');

        $this->generate(InvokableAction::class, $this->createRoutes(
            Route::get($pattern)->action(InvokableAction::class),
        ));
    }

    public static function unsupportedPatterns(): array
    {
        return [
            'regex' => ['/items/{id:\\d+}'],
            'optional' => ['/items[/{id}]'],
            'relative' => ['items'],
            'query' => ['/items?type=all'],
            'fragment' => ['/items#details'],
            'malformed placeholder' => ['/items/{id'],
            'invalid encoding' => ['/items/%xy'],
        ];
    }

    public function testClassLevelOperationRequiresMethodOrExplicitPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Annotate the method or set an explicit path');

        $this->generate(ClassOperation::class, $this->createRoutes(
            Route::get('/class')->action(ClassOperation::class),
        ));
    }

    private function createRoutes(Route|Group ...$routes): RouteCollection
    {
        $collector = new RouteCollector();
        $collector->addRoute(...$routes);
        return new RouteCollection($collector);
    }

    /**
     * @param class-string $fixture
     */
    private function generate(string $fixture, RouteCollection $routes): array
    {
        $logger = new SimpleLogger();
        $generator = new Generator($logger);
        $generator->getProcessorPipeline()->insert(new RoutePath($routes), BuildPaths::class);
        $service = new SwaggerService(new Aliases(), $generator);
        $filename = __DIR__ . '/Fixture/RoutePath/' . (new ReflectionClass($fixture))->getShortName() . '.php';

        $document = json_decode($service->fetch([$filename])->toJson(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([], array_filter(
            $logger->getMessages(),
            static fn(array $message): bool => $message['level'] !== 'debug',
        ));
        return $document;
    }
}
