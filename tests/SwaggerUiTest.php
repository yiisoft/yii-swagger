<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Http\Method;
use Yiisoft\Log\Logger;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Yii\View\ViewRenderer;
use Yiisoft\View\WebView;
use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Swagger\Tests\Mock\MockCsrfTokenStorage;
use Yiisoft\Yii\View\CsrfViewInjection;

final class SwaggerUiTest extends TestCase
{
    public function testSwaggerUiMiddleware(): void
    {
        $middleware = $this->createMiddleware();

        $this->assertNotSame($middleware, $middleware->withJsonUrl('/'));

        $response = $middleware
            ->process($this->createServerRequest(), $this->createRequestHandler());

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function createContainer(): ContainerInterface
    {
        $definitions = [
            Aliases::class => new Aliases(),
            CacheInterface::class => new ArrayCache(),
            DataResponseFactoryInterface::class => DataResponseFactory::class,
            ResponseFactoryInterface::class => Psr17Factory::class,
        ];

        return new Container($definitions);
    }

    private function createMiddleware(): SwaggerUi
    {
        $container = $this->createContainer();

        $viewRenderer = new ViewRenderer(
            $container->get(DataResponseFactoryInterface::class),
            $container->get(Aliases::class),
            $this->createMock(WebView::class),
            $this->getCsrfViewInjection(),
            __DIR__,
            '',
            []
        );

        return new SwaggerUi(
            $container->get(ViewRenderer::class),
            $container->get(SwaggerService::class)
        );
    }

    private function getCsrfViewInjection(): CsrfViewInjection {
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
