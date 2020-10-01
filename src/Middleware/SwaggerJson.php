<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Middleware;

use DateInterval;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Swagger\Service\SwaggerService;

use function md5;
use function var_export;

final class SwaggerJson implements MiddlewareInterface
{
    private array $annotationPaths = [];
    private bool $enableCache = false;
    private CacheInterface $cache;
    private DataResponseFactoryInterface $responseFactory;
    private SwaggerService $swaggerService;

    /** @var DateInterval|int|null $cacheTTL */
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
        $cacheKey = $this->enableCache ? $this->getCacheKey() : false;

        if ($cacheKey && $this->cache->has($cacheKey)) {
            return $this->responseFactory
                ->createResponse($this->cache->get($cacheKey));
        }

        $openApi = $this->swaggerService
            ->fetch($this->annotationPaths);

        if ($cacheKey) {
            $this->cache->set($cacheKey, $openApi, $this->cacheTTL);
        }

        return $this->responseFactory
            ->createResponse($openApi);
    }

    private function getCacheKey(): string
    {
        return md5(var_export([self::class, $this->annotationPaths], true));
    }

    public function withAnnotationPaths(array $annotationPaths): self
    {
        $new = clone $this;
        $new->annotationPaths = $annotationPaths;
        return $new;
    }

    /**
     * @param DateInterval|int|null $cacheTTL
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
