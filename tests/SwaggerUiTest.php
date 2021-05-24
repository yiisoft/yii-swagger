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
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetLoaderInterface;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\Synchronizer\Generator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Http\Method;
use Yiisoft\Swagger\Asset\SwaggerUiAsset;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Swagger\Tests\Mock\MockCsrfTokenStorage;
use Yiisoft\Swagger\Tests\Spy\AssetLoaderSpy;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\CsrfViewInjection;
use Yiisoft\Yii\View\ViewRenderer;

final class SwaggerUiTest extends TestCase
{
    public function testSwaggerUiMiddlewareWithUrl(): void
    {
        $middleware = $this->createMiddleware($this->createContainer());
        $this->assertNotSame($middleware, $middleware->withJsonUrl('/'));
    }

    public function testSwaggerUiMiddleware(): void
    {
        $container = $this->createContainer();
        $middleware = $this->createMiddleware($container)->withJsonUrl('/');

        $request = $this->createServerRequest();
        $handler = $this->createRequestHandler();

        $response = $middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());

        /** @var AssetLoaderSpy $assetSpy */
        $assetSpy = $container->get(AssetLoaderInterface::class);
        $this->assertEquals([SwaggerUiAsset::class], $assetSpy->getLoaded());
    }

    private function createContainer(): ContainerInterface
    {
        $definitions = [
            AssetLoaderInterface::class => new AssetLoaderSpy(),
            AssetManager::class => static function (Aliases $aliases, AssetLoaderInterface $assetLoader) {
                return new AssetManager($aliases, $assetLoader);
            },
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
                    $this->createWebView(),
                    __DIR__,
                    '',
                    [$this->getCsrfViewInjection()]
                );
            },
        ];

        return new Container($definitions);
    }

    private function createMiddleware(ContainerInterface $container): SwaggerUi
    {
        return new SwaggerUi(
            $container->get(ViewRenderer::class),
            $container->get(SwaggerService::class),
            $container->get(AssetManager::class)
        );
    }

    private function getCsrfViewInjection(): CsrfViewInjection
    {
        $csrfToken = $this->createCsrfToken();

        return new CsrfViewInjection($csrfToken);
    }

    private function createCsrfToken(string $token = null): CsrfTokenInterface
    {
        $generator = new RandomCsrfTokenGenerator();
        $storage = $this->createMock(MockCsrfTokenStorage::class);
        if ($token !== null) {
            $storage
                ->expects($this->once())
                ->method('get')
                ->willReturn($token);
        }
        return new SynchronizerCsrfToken($generator, $storage);
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

    private function createWebView(): WebView
    {
        return new WebView(__DIR__, new SimpleEventDispatcher(), new NullLogger());
    }
}
