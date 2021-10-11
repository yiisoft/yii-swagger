<?php

use Yiisoft\Assets\AssetManager;use Yiisoft\Json\Json;use Yiisoft\Swagger\Asset\SwaggerUiAsset;

/**
 * @var AssetManager $assetManager
 * @var string $content
 * @var array $params
 */

$assetManager->register(
    [
        SwaggerUiAsset::class
    ]
);

$this->addCssFiles($assetManager->getCssFiles());
$this->addJsFiles($assetManager->getJsFiles());

$this->beginPage();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Swagger UI</title>
    <?php
    $this->head() ?>
</head>
<body style="margin:0;">
<?php
$this->beginBody(); ?>

<div id="swagger-ui"></div>
<script>
    window.onload = function () {
        // Begin Swagger UI call region
        window.ui = SwaggerUIBundle({
            <?php foreach ($params as $key => $val) {
                if (in_array($key, ['presets', 'plugins']) && count($val) > 0) {
                    echo $key . ': [' . implode(',', $val) . '],';
                    continue;
                }

                echo $key . ':' . Json::encode($val) . ',';
            }?>
        })
    }
</script>

<?php
$this->endBody(); ?>
</body>
</html>
<?php
$this->endPage(true);

