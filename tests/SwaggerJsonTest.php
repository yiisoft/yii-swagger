<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Http\Method;
use Yiisoft\Swagger\Middleware\SwaggerJson;
use Yiisoft\Swagger\Service\SwaggerService;

final class SwaggerJsonTest extends TestCase
{
    public function testSwaggerJsonMiddleware(): void
    {
        $middleware = $this->createMiddleware();

        $this->assertNotSame($middleware, $middleware->withAnnotationPaths([]));
        $this->assertNotSame($middleware, $middleware->withCache());

        $response = $middleware
            ->withAnnotationPaths([__DIR__ . '/Mock'])
            ->process($this->createServerRequest(), $this->createRequestHandler());

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function createMiddleware(): SwaggerJson
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
        $definitions = [
            Aliases::class => new Aliases(),
            CacheInterface::class => new ArrayCache(),
            DataResponseFactoryInterface::class => DataResponseFactory::class,
            ResponseFactoryInterface::class => Psr17Factory::class,
            StreamFactoryInterface::class => Psr17Factory::class,
        ];

        return new Container($definitions);
    }

    private function createServerRequest(string $method = Method::GET, $headers = []): ServerRequestInterface
    {
        return new ServerRequest($method, '/', $headers);
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn(new Response(200));

        return $requestHandler;
    }
}
