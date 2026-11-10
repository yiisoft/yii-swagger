<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'Class operation', version: '1.0')]
#[OA\Get(responses: [new OA\Response(response: '200', description: 'Class response')])]
final class ClassOperation {}
