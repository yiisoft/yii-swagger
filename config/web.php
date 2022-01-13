<?php

declare(strict_types=1);

use Yiisoft\Swagger\Middleware\SwaggerJson;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Service\SwaggerService;

/** @var array $params */

return [
    SwaggerService::class => SwaggerService::class,
    SwaggerUi::class => [
        '__construct()' => [
            'params' => $params['yiisoft/yii-swagger']['ui-params'],
        ],
    ],
    SwaggerJson::class => [
        'withAnnotationPaths()' => [...$params['yiisoft/yii-swagger']['annotation-paths']],
        'withCache()' => [$params['yiisoft/yii-swagger']['cacheTTL']],
    ],
];
