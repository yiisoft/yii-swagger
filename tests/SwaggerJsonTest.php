<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactoryInterface;
use Yiisoft\Yii\Swagger\Action\SwaggerJson;
use Yiisoft\Yii\Swagger\Service\SwaggerService;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class SwaggerJsonTest extends TestCase
{
    public function testSwaggerJsonMiddleware(): void
    {
        $action = $this->createAction();

        $this->assertNotSame($action, $action->withPaths());
        $this->assertNotSame($action, $action->withCache());

        $response = $action
            ->withPaths(__DIR__ . '/Support')
            ->handle($this->createServerRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSwaggerJsonMiddlewareWithCache(): void
    {
        $action = $this->createAction();

        $response = $action
            ->withPaths(__DIR__ . '/Support')
            ->withCache(120)
            ->handle($this->createServerRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    private function createAction(): SwaggerJson
    {
        $container = $this->createContainer();

        return new SwaggerJson(
            $container->get(CacheInterface::class),
            $container->get(DataResponseFactoryInterface::class),
            $container->get(SwaggerService::class),
        );
    }

    private function createContainer(): ContainerInterface
    {
        $aliases = new Aliases();

        return new SimpleContainer([
            Aliases::class => $aliases,
            CacheInterface::class => new Cache(new ArrayCache()),
            DataResponseFactoryInterface::class => new DataResponseFactory(new ResponseFactory()),
            SwaggerService::class => new SwaggerService($aliases),
        ]);
    }

    private function createServerRequest(): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest('GET', '/');
    }
}
