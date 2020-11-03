<?php

namespace Yiisoft\Swagger\Asset;

use Yiisoft\Assets\AssetBundle;

class SwaggerUiAsset extends AssetBundle
{
    public ?string $basePath = '@assets';

    public ?string $baseUrl = '@assetsUrl';

    public array $css = [
        'swagger-ui.css',
    ];
    public array $js = [
        'swagger-ui-bundle.js',
        'swagger-ui-standalone-preset.js',
    ];

    public function __construct()
    {
        $this->sourcePath = '@vendor/swagger-api/swagger-ui/dist';
    }
}
