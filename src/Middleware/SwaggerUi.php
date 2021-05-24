<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Yii\View\ViewRenderer;

final class SwaggerUi implements MiddlewareInterface
{
    private string $jsonUrl = '/';
    private SwaggerService $swaggerService;
    private ViewRenderer $viewRenderer;
    private AssetManager $assetManager;

    public function __construct(ViewRenderer $viewRenderer, SwaggerService $swaggerService, AssetManager $assetManager)
    {
        $this->swaggerService = $swaggerService;
        $this->viewRenderer = $viewRenderer;
        $this->assetManager = $assetManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->viewRenderer
            ->withViewPath($this->swaggerService->getViewPath())
            ->renderPartial(
                $this->swaggerService->getViewName(),
                [
                    'jsonUrl' => $this->jsonUrl,
                    'assetManager' => $this->assetManager
                ]
            );
    }

    public function withJsonUrl(string $jsonUrl): self
    {
        $new = clone $this;
        $new->jsonUrl = $jsonUrl;
        return $new;
    }
}
