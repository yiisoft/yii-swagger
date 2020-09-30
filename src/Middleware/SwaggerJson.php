<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Swagger\Service\SwaggerService;

final class SwaggerJson implements MiddlewareInterface
{
    private DataResponseFactoryInterface  $responseFactory;
    private array $annotationPaths;
    private SwaggerService $swaggerService;

    public function __construct(
        DataResponseFactoryInterface $responseFactory,
        SwaggerService $swaggerService
    ) {
        $this->responseFactory = $responseFactory;
        $this->swaggerService = $swaggerService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $openApi = $this->swaggerService
            ->fetch($this->annotationPaths);

        return $this->responseFactory
            ->createResponse($openApi);
    }

    public function withAnnotationPaths(array $annotationPaths): self
    {
        $new = clone $this;
        $new->annotationPaths = $annotationPaths;
        return $new;
    }

}
