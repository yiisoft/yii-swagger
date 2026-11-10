<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath;

use LogicException;
use OpenApi\Attributes as OA;

#[OA\Info(title: 'Route paths', version: '1.0')]
final class InvokableAction
{
    public function __construct()
    {
        throw new LogicException('Generating documentation must not instantiate the action.');
    }

    #[OA\Get(
        operationId: 'read-item',
        responses: [new OA\Response(response: '200', description: 'Item response')],
    )]
    public function __invoke(): void {}
}
