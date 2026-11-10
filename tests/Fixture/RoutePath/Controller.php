<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath;

use LogicException;
use OpenApi\Attributes as OA;

#[OA\Info(title: 'Controller route paths', version: '1.0')]
final class Controller
{
    public function __construct()
    {
        throw new LogicException('Generating documentation must not instantiate the controller.');
    }

    #[OA\Get(
        operationId: 'read-item',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: '200', description: 'Item response')],
    )]
    #[OA\Post(
        operationId: 'write-item',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: '201', description: 'Created item')],
    )]
    public function item(): void {}
}
