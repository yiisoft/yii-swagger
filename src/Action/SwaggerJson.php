<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Action;

use DateInterval;
use OpenApi\Annotations\OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Yii\Swagger\Service\SwaggerService;

final class SwaggerJson implements RequestHandlerInterface
{
    private array $paths = [];
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
            $openApi = $this->swaggerService->fetch($this->paths);
        } else {
            /** @var OpenApi $openApi */
            $openApi = $this->cache->getOrSet(
                [self::class, $this->paths],
                fn() => $this->swaggerService->fetch($this->paths),
                $this->cacheTTL,
            );
        }

        return $this->responseFactory->createResponse($openApi);
    }

    public function withPaths(string ...$paths): self
    {
        $new = clone $this;
        $new->paths = $paths;
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
