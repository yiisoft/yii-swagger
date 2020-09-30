<?php

namespace Yiisoft\Swagger\Tests\Mock;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @OA\Info(title="Yii test api", version="1.0")
 */
class ApiMock
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     @OA\Response(response="200", description="Test api response")
     * )
     */
    public function mockResponse(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
    }
}
