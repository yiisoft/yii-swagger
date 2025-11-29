<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Support;

use Yiisoft\Assets\AssetBundle;
use Yiisoft\Assets\AssetLoaderInterface;

final class AssetLoaderSpy implements AssetLoaderInterface
{
    private array $loaded = [];

    public function getAssetUrl(AssetBundle $bundle, string $assetPath): string
    {
        return '';
    }

    public function loadBundle(string $name, array $config = []): AssetBundle
    {
        $this->loaded[] = $name;
        return new $name();
    }

    public function getLoaded(): array
    {
        return $this->loaded;
    }
}
