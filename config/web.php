<?php

declare(strict_types=1);

use Yiisoft\Swagger\Middleware\SwaggerUI;
use Yiisoft\Swagger\Service\SwaggerService;

return [
    SwaggerService::class => SwaggerService::class,
    SwaggerUI::class => [
        '__construct()' => [
            'params' => $params['yiisoft/yii-swagger']['ui-params'],
        ],
    ],
];
