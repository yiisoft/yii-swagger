<?php

namespace Yiisoft\Swagger\Asset;

use Yiisoft\Assets\AssetBundle;

class SwaggerUiAsset extends AssetBundle
{
    public ?string $basePath = '@public';
    public ?string $baseUrl = '@web';

    public array $css = [
        'swagger-ui.css',
    ];
    public array $js = [
        'swagger-ui-bundle.js',
        'swagger-ui-standalone-preset.js',
    ];

    public function __construct()
    {
        $this->sourcePath = dirname(__DIR__, 4) . '/swagger-api/swagger-ui/dist';
    }
}
