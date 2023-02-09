<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Assets\AssetLoaderInterface;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Swagger\Middleware\SwaggerJson;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\ViewRenderer;

final class ConfigTest extends TestCase
{
    public function testDiWeb(): void
    {
        $container = $this->createContainer('web');

        $swaggerService = $container->get(SwaggerService::class);
        $swaggerUi = $container->get(SwaggerUi::class);
        $swaggerJson = $container->get(SwaggerJson::class);

        $this->assertInstanceOf(SwaggerService::class, $swaggerService);
        $this->assertInstanceOf(SwaggerUi::class, $swaggerUi);
        $this->assertInstanceOf(SwaggerJson::class, $swaggerJson);
    }

    private function createContainer(?string $postfix = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($postfix)
                +
                [
                    DataResponseFactoryInterface::class => $this->createMock(DataResponseFactoryInterface::class),
                    WebView::class => new WebView(__DIR__, new SimpleEventDispatcher()),
                    ViewRenderer::class => [
                        '__construct()' => ['viewPath' => __DIR__],
                    ],
                    AssetLoaderInterface::class => $this->createMock(AssetLoaderInterface::class),
                    CacheInterface::class => $this->createMock(CacheInterface::class),
                ]
            )
        );
    }

    private function getDiConfig(?string $postfix = null): array
    {
        $params = $this->getParams();
        return require dirname(__DIR__) . '/config/di' . ($postfix !== null ? '-' . $postfix : '') . '.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
