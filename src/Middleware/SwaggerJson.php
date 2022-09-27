<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Middleware;

use DateInterval;
use OpenApi\Annotations\OpenApi;
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
    private DateInterval|int|null $cacheTTL = null;

    public function __construct(private CacheInterface $cache, private DataResponseFactoryInterface $responseFactory, private SwaggerService $swaggerService)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var OpenApi $openApi */
        $openApi = !$this->enableCache ? $this->swaggerService->fetch($this->annotationPaths) : $this->cache->getOrSet(
            [self::class, $this->annotationPaths],
            fn () => $this->swaggerService->fetch($this->annotationPaths),
            $this->cacheTTL,
        );

        return $this->responseFactory->createResponse($openApi);
    }

    public function withAnnotationPaths(string ...$annotationPaths): self
    {
        $new = clone $this;
        $new->annotationPaths = $annotationPaths;
        return $new;
    }

    /**
     * @param DateInterval|int|null $cacheTTL
     */
    public function withCache(DateInterval|int $cacheTTL = null): self
    {
        $new = clone $this;
        $new->enableCache = true;
        $new->cacheTTL = $cacheTTL;
        return $new;
    }
}
