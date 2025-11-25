<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Action;

use DateInterval;
use OpenApi\Annotations\OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Swagger\Service\SwaggerService;

final class SwaggerJson implements RequestHandlerInterface
{
    private array $annotationPaths = [];
    private bool $enableCache = false;
    private DateInterval|int|null $cacheTTL = null;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly DataResponseFactoryInterface $responseFactory,
        private readonly SwaggerService $swaggerService,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->enableCache) {
            $openApi = $this->swaggerService->fetch($this->annotationPaths);
        } else {
            /** @var OpenApi $openApi */
            $openApi = $this->cache->getOrSet(
                [self::class, $this->annotationPaths],
                fn() => $this->swaggerService->fetch($this->annotationPaths),
                $this->cacheTTL,
            );
        }

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
