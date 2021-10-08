<?php

declare(strict_types=1);

use Yiisoft\Swagger\Middleware\SwaggerUI;

return [
    SwaggerUI::class => [
        '__construct()' => [
            'options' => $params['yiisoft/yii-swagger']['options'],
        ],
    ],
];
