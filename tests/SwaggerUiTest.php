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
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Csrf\CsrfToken;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Http\Method;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Swagger\Tests\Mock\MockCsrfTokenStorage;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\CsrfViewInjection;
use Yiisoft\Yii\View\ViewRenderer;

final class SwaggerUiTest extends TestCase
{
    public function testSwaggerUiMiddlewareWithUrl(): void
    {
        $middleware = $this->createMiddleware();
        $this->assertNotSame($middleware, $middleware->withJsonUrl('/'));
    }

    public function testSwaggerUiMiddleware(): void
    {
        $middleware = $this->createMiddleware()->withJsonUrl('/');

        $request = $this->createServerRequest();
        $handler = $this->createRequestHandler();

        $response = $middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function createContainer(): ContainerInterface
    {
        $definitions = [
            Aliases::class => new Aliases(),
            CacheInterface::class => new ArrayCache(),
            DataResponseFactoryInterface::class => DataResponseFactory::class,
            ResponseFactoryInterface::class => Psr17Factory::class,
            ViewRenderer::class => function (
                DataResponseFactoryInterface $dataResponseFactory,
                Aliases $aliases
            ) {
                return new ViewRenderer(
                    $dataResponseFactory,
                    $aliases,
                    $this->createMock(WebView::class),
                    $this->getCsrfViewInjection(),
                    __DIR__,
                    '',
                    []
                );
            },
        ];

        return new Container($definitions);
    }

    private function createMiddleware(): SwaggerUi
    {
        $container = $this->createContainer();


        return new SwaggerUi(
            $container->get(ViewRenderer::class),
            $container->get(SwaggerService::class)
        );
    }

    private function getCsrfViewInjection(): CsrfViewInjection
    {
        $csrfToken = $this->createCsrfToken();

        return new CsrfViewInjection($csrfToken);
    }

    private function createCsrfToken(string $token = null): CsrfToken
    {
        $mock = $this->createMock(MockCsrfTokenStorage::class);
        if ($token !== null) {
            $mock
                ->expects($this->once())
                ->method('get')
                ->willReturn($token);
        }
        return new CsrfToken($mock);
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
