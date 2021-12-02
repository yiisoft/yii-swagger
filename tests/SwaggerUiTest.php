<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetLoaderInterface;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\Csrf\Synchronizer\Generator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Swagger\Asset\SwaggerUiAsset;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Swagger\Tests\Support\CsrfTokenStorage;
use Yiisoft\Swagger\Tests\Support\AssetLoaderSpy;
use Yiisoft\Test\Support\Container\SimpleContainer;
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
        $response->getBody();

        $this->assertSame(200, $response->getStatusCode());

        /** @var AssetLoaderSpy $assetSpy */
        $assetSpy = $container->get(AssetLoaderInterface::class);
        $this->assertSame([SwaggerUiAsset::class], $assetSpy->getLoaded());
    }

    private function createContainer(): ContainerInterface
    {
        $aliases = new Aliases();
        $assetLoader = new AssetLoaderSpy();
        $dataResponseFactory = new DataResponseFactory(new ResponseFactory(), new StreamFactory());

        return new SimpleContainer([
            Aliases::class => $aliases,
            AssetLoaderInterface::class => $assetLoader,
            AssetManager::class => new AssetManager($aliases, $assetLoader),
            CacheInterface::class => new Cache(new ArrayCache()),
            DataResponseFactoryInterface::class => $dataResponseFactory,
            SwaggerService::class => new SwaggerService($aliases),
            ViewRenderer::class => new ViewRenderer(
                $dataResponseFactory,
                $aliases,
                $this->createWebView(),
                __DIR__,
                '',
                [$this->getCsrfViewInjection()],
            ),
        ]);
    }

    private function createMiddleware(ContainerInterface $container): SwaggerUi
    {
        return new SwaggerUi(
            $container->get(ViewRenderer::class),
            $container->get(SwaggerService::class),
            $container->get(AssetManager::class),
            [],
        );
    }

    private function getCsrfViewInjection(): CsrfViewInjection
    {
        $csrfToken = new SynchronizerCsrfToken(new RandomCsrfTokenGenerator(), new CsrfTokenStorage());
        $csrfMiddleware = new CsrfMiddleware(new ResponseFactory(), $csrfToken);

        return new CsrfViewInjection($csrfToken, $csrfMiddleware);
    }

    private function createServerRequest(): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest('GET', '/');
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->method('handle')->willReturn((new ResponseFactory())->createResponse());

        return $requestHandler;
    }

    private function createWebView(): WebView
    {
        return new WebView(__DIR__, new SimpleEventDispatcher());
    }
}
