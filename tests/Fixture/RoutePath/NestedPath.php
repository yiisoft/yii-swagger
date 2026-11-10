<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'Nested explicit path', version: '1.0')]
#[OA\PathItem(path: '/nested', get: new OA\Get(
    responses: [new OA\Response(response: '200', description: 'Nested response')],
))]
final class NestedPath {}
