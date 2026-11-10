<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests;

use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\RouteCollector;
use Yiisoft\Yii\Swagger\Factory\RoutePathGeneratorFactory;
use Yiisoft\Yii\Swagger\Service\SwaggerService;
use Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath\InvokableAction;

use function array_keys;
use function dirname;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class RoutePathGeneratorFactoryTest extends TestCase
{
    public function testDiFactoryUsesApplicationRoutesWithPackageServiceConfiguration(): void
    {
        $routes = $this->createRoutes('/items');
        $params = require dirname(__DIR__) . '/config/params.php';
        $definitions = require dirname(__DIR__) . '/config/di-web.php';
        $container = new Container(ContainerConfig::create()->withDefinitions([
            Generator::class => RoutePathGeneratorFactory::create(...),
            RouteCollectionInterface::class => $routes,
            Aliases::class => new Aliases(),
        ] + $definitions));

        $document = $this->generate($container->get(SwaggerService::class));

        $this->assertSame(['/api/items'], array_keys($document['paths']));
        $this->assertSame('read-item', $document['paths']['/api/items']['get']['operationId']);
        $this->assertSame('Item response', $document['paths']['/api/items']['get']['responses']['200']['description']);
    }

    public function testFactoryCallsKeepRoutesAndGeneratorOptionsIndependent(): void
    {
        $first = RoutePathGeneratorFactory::create($this->createRoutes('/first'))->setVersion('3.1.0');
        $second = RoutePathGeneratorFactory::create($this->createRoutes('/second'));
        $firstService = new SwaggerService(new Aliases(), $first);

        $firstDocument = $this->generate($firstService);
        $secondDocument = $this->generate(new SwaggerService(new Aliases(), $second));
        $firstAgain = $this->generate($firstService);

        $this->assertSame('3.1.0', $firstDocument['openapi']);
        $this->assertSame(['/api/first'], array_keys($firstDocument['paths']));
        $this->assertSame('3.0.0', $secondDocument['openapi']);
        $this->assertSame(['/api/second'], array_keys($secondDocument['paths']));
        $this->assertSame($firstDocument, $firstAgain);
    }

    private function createRoutes(string $path): RouteCollection
    {
        $collector = new RouteCollector();
        $collector->addRoute(Group::create('/api')->routes(Route::get($path)->action(InvokableAction::class)));
        return new RouteCollection($collector);
    }

    private function generate(SwaggerService $service): array
    {
        return json_decode(
            $service->fetch([__DIR__ . '/Fixture/RoutePath/InvokableAction.php'])->toJson(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }
}
