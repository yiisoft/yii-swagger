<?php

use Yiisoft\Swagger\Asset\SwaggerUiAsset;

/**
 * @var \Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var string $apiUrl
 */

$assetManager->register(
    [
        SwaggerUiAsset::class
    ]
);

$this->setCssFiles($assetManager->getCssFiles());
$this->setJsFiles($assetManager->getJsFiles());

$this->beginPage();
?><!DOCTYPE html>
<html lang="">
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
        let ui = SwaggerUIBundle({
            url: '<?= $jsonUrl; ?>',
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout"
        });
        window.ui = ui
    }
</script>

<?php
$this->endBody(); ?>
</body>
</html>
<?php
$this->endPage(true);