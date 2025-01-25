<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Asset;

use Yiisoft\Assets\AssetBundle;
use Yiisoft\Assets\AssetManager;

/**
 * @psalm-import-type CssFile from AssetManager
 * @psalm-import-type JsFile from AssetManager
 */
final class SwaggerUiAsset extends AssetBundle
{
    public ?string $basePath = '@assets';

    public ?string $baseUrl = '@assetsUrl';

    /**
     * @psalm-var array<string|CssFile>
     */
    public array $css = [
        'swagger-ui.css',
    ];

    /**
     * @psalm-var array<string|JsFile>
     */
    public array $js = [
        'swagger-ui-bundle.js',
        'swagger-ui-standalone-preset.js',
    ];

    public function __construct()
    {
        $this->sourcePath = '@vendor/swagger-api/swagger-ui/dist';
    }
}
