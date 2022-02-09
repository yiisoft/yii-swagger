<?php

declare(strict_types=1);

use Yiisoft\Cache\CacheInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Swagger\Middleware\SwaggerJson;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Service\SwaggerService;

/** @var array $params */

return [
    SwaggerService::class => [
        'withOptions()' => [
            $params['yiisoft/yii-swagger']['swagger-options'],
        ],
    ],

    SwaggerUi::class => [
        '__construct()' => [
            'params' => $params['yiisoft/yii-swagger']['ui-params'],
        ],
    ],

    SwaggerJson::class => static function (
        CacheInterface $cache,
        DataResponseFactoryInterface $responseFactory,
        SwaggerService $swaggerService
    ) use ($params) {
        $params = $params['yiisoft/yii-swagger'];
        $swaggerJson = new SwaggerJson($cache, $responseFactory, $swaggerService);

        if (array_key_exists('cacheTTL', $params)) {
            $swaggerJson = $swaggerJson->withCache($params['cacheTTL']);
        }

        return $swaggerJson->withAnnotationPaths(...$params['annotation-paths']);
    },
];
