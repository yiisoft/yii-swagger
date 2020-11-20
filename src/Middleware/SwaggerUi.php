<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Yii\View\ViewRenderer;

final class SwaggerUi implements MiddlewareInterface
{
    private string $jsonUrl = '/';
    private SwaggerService $swaggerService;
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer, SwaggerService $swaggerService)
    {
        $this->swaggerService = $swaggerService;
        $this->viewRenderer = $viewRenderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->viewRenderer
            ->withViewPath($this->swaggerService->getViewPath())
            ->renderPartial(
                $this->swaggerService->getViewName(),
                [
                    'jsonUrl' => $this->jsonUrl,
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
