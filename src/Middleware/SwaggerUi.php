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
    private array $defaultOptions = [
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
    private array $options;

    public function __construct(
        ViewRenderer $viewRenderer,
        SwaggerService $swaggerService,
        AssetManager $assetManager,
        array $options
    ) {
        $this->swaggerService = $swaggerService;
        $this->viewRenderer = $viewRenderer;
        $this->assetManager = $assetManager;
        $this->options = $options;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $options = ArrayHelper::merge($this->defaultOptions, $this->options);
        $options['url'] = $this->jsonUrl;

        return $this->viewRenderer
            ->withViewPath($this->swaggerService->getViewPath())
            ->renderPartial($this->swaggerService->getViewName(), [
                'assetManager' => $this->assetManager,
                'options' => $options,
            ]);
    }

    public function withJsonUrl(string $jsonUrl): self
    {
        $new = clone $this;
        $new->jsonUrl = $jsonUrl;
        return $new;
    }
}
