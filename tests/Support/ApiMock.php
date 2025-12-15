<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Support;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[OA\Info(version: "1.0", title: "Yii test api")]
#[OA\Schema(schema: "TestResponse", properties: [], type: "object")]
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
