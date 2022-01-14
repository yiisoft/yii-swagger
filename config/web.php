<?php

declare(strict_types=1);

use Yiisoft\Injector\Injector;
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
    SwaggerJson::class => static function (Injector $injector) use ($params) {
        $params = $params['yiisoft/yii-swagger'];
        $swaggerJson = $injector->make(SwaggerJson::class);
        if (array_key_exists('cacheTTL', $params)) {
            $swaggerJson = $swaggerJson->withCache($params['cacheTTL']);
        }
        return $swaggerJson->withAnnotationPaths(...$params['annotation-paths']);
    },
];
