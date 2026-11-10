<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'Explicit route path', version: '1.0')]
final class ExplicitAction
{
    #[OA\Get(path: '/explicit', responses: [new OA\Response(response: '200', description: 'Explicit response')])]
    public function __invoke(): void {}
}
