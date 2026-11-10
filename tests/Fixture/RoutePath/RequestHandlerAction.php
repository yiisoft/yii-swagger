<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath;

use LogicException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[OA\Info(title: 'Request handler route paths', version: '1.0')]
final class RequestHandlerAction implements RequestHandlerInterface
{
    public function __construct()
    {
        throw new LogicException('Generating documentation must not instantiate the request handler.');
    }

    #[OA\Get(responses: [new OA\Response(response: '200', description: 'Handler response')])]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new LogicException('Generating documentation must not call the request handler.');
    }
}
