<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Yii\View\ViewRenderer;

final class SwaggerUi implements MiddlewareInterface
{
    private array $defaultParams = [
        'dom_id' => '#swagger-ui',
        'deepLinking' => true,
        'presets' => [
            'SwaggerUIBundle.presets.apis',
            'SwaggerUIStandalonePreset',
        ],
        'plugins' => [
            'SwaggerUIBundle.plugins.DownloadUrl',
        ],
        'layout' => 'StandaloneLayout',
    ];
    private string $jsonUrl = '/';
    private SwaggerService $swaggerService;
    private ViewRenderer $viewRenderer;
    private AssetManager $assetManager;
    private array $params;

    public function __construct(
        ViewRenderer $viewRenderer,
        SwaggerService $swaggerService,
        AssetManager $assetManager,
        array $params
    ) {
        $this->swaggerService = $swaggerService;
        $this->viewRenderer = $viewRenderer;
        $this->assetManager = $assetManager;
        $this->params = $params;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = ArrayHelper::merge($this->defaultParams, $this->params);
        $params['url'] = $this->jsonUrl;

        return $this->viewRenderer
            ->withViewPath($this->swaggerService->getViewPath())
            ->renderPartial($this->swaggerService->getViewName(), [
                'assetManager' => $this->assetManager,
                'params' => $params,
            ]);
    }

    public function withJsonUrl(string $jsonUrl): self
    {
        $new = clone $this;
        $new->jsonUrl = $jsonUrl;
        return $new;
    }
}
