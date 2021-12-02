<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Middleware;

use DateInterval;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Cache\CacheInterface;

use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Swagger\Service\SwaggerService;

final class SwaggerJson implements MiddlewareInterface
{
    private array $annotationPaths = [];
    private bool $enableCache = false;
    private CacheInterface $cache;
    private DataResponseFactoryInterface $responseFactory;
    private SwaggerService $swaggerService;

    /** @var DateInterval|int|null */
    private $cacheTTL;

    public function __construct(
        CacheInterface $cache,
        DataResponseFactoryInterface $responseFactory,
        SwaggerService $swaggerService
    ) {
        $this->cache = $cache;
        $this->responseFactory = $responseFactory;
        $this->swaggerService = $swaggerService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $openApi = !$this->enableCache ? $this->swaggerService->fetch($this->annotationPaths) : $this->cache->getOrSet(
            [self::class, $this->annotationPaths],
            static fn () => $this->swaggerService->fetch($this->annotationPaths),
            $this->cacheTTL,
        );

        return $this->responseFactory->createResponse($openApi);
    }

    public function withAnnotationPaths(array $annotationPaths): self
    {
        $new = clone $this;
        $new->annotationPaths = $annotationPaths;
        return $new;
    }

    /**
     * @param DateInterval|int|null $cacheTTL
     *
     * @return SwaggerJson
     */
    public function withCache($cacheTTL = null): self
    {
        $new = clone $this;
        $new->enableCache = true;
        $new->cacheTTL = $cacheTTL;
        return $new;
    }
}
