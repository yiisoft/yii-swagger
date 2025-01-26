<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Swagger\Service\SwaggerService;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class SwaggerUi implements RequestHandlerInterface
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

    public function __construct(
        private readonly ViewRenderer $viewRenderer,
        private readonly SwaggerService $swaggerService,
        private readonly AssetManager $assetManager,
        private readonly array $params
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
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
