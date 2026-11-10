<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Tests\Fixture\RoutePath;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[OA\Info(title: 'Terminal middleware', version: '1.0')]
final class TerminalMiddleware implements MiddlewareInterface
{
    #[OA\Get(responses: [new OA\Response(response: '200', description: 'Invokable response')])]
    public function __invoke(): void {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
