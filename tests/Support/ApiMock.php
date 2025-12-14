<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Support;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[OA\Info("Yii test api", "1.0")]
#[OA\Schema(schema: "TestResponse", type: "object", properties: [])]
final class ApiMock
{
    #[OA\Get(
        path: "/api/test",
        responses: [
            new OA\Response(response: "200", description: "Test api response"),
        ],
    )]
    public function mockResponse(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {}
}
