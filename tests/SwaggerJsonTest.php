<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Swagger\Action\SwaggerJson;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class SwaggerJsonTest extends TestCase
{
    public function testSwaggerJsonMiddleware(): void
    {
        $action = $this->createAction();

        $this->assertNotSame($action, $action->withAnnotationPaths());
        $this->assertNotSame($action, $action->withCache());

        /** @var DataResponse $response */
        $response = $action
            ->withAnnotationPaths(__DIR__ . '/Support')
            ->handle($this->createServerRequest());

        $this->assertSame(200, $response->getStatusCode());
    }

    private function createAction(): SwaggerJson
    {
        $container = $this->createContainer();

        return new SwaggerJson(
            $container->get(CacheInterface::class),
            $container->get(DataResponseFactoryInterface::class),
            $container->get(SwaggerService::class)
        );
    }

    private function createContainer(): ContainerInterface
    {
        $aliases = new Aliases();

        return new SimpleContainer([
            Aliases::class => $aliases,
            CacheInterface::class => new Cache(new ArrayCache()),
            DataResponseFactoryInterface::class => new DataResponseFactory(new ResponseFactory(), new StreamFactory()),
            SwaggerService::class => new SwaggerService($aliases),
        ]);
    }

    private function createServerRequest(): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest('GET', '/');
    }
}
