<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use OpenApi\Annotations\OpenApi;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;

final class SwaggerService
{

    private Aliases $aliases;
    private CacheInterface $cache;

    private bool $cacheJsonSchema = false;
    private string $viewPath;
    private string $viewName = 'swagger-ui';

    public function __construct(ContainerInterface $container)
    {
        $this->aliases = $container->get(Aliases::class);
        $this->cache = $container->get(CacheInterface::class);

        $this->viewPath = $this->getDefaultViewPath();
    }

    private function getDefaultViewPath(): string
    {
        return dirname(__DIR__, 2) . '/views';
    }

    public function getViewPath(): string
    {
        return $this->aliases->get($this->viewPath);
    }

    public function getViewName(): string
    {
        return $this->viewName;
    }

    public function withViewPath(string $viewPath): self
    {
        $new = clone $this;
        $new->viewPath = $viewPath;
        return $new;
    }

    public function withViewName(string $viewName): self
    {
        $new = clone $this;
        $new->viewName = $viewName;
        return $new;
    }

    public function fetch(array $annotationPaths): OpenApi
    {
        if ($this->cacheJsonSchema) {
            $cacheKey = $this->getCacheKey($annotationPaths);
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }

        $directories = \array_map(fn(string $path) => $this->aliases->get($path), $annotationPaths);
        $openApi = \OpenApi\scan($directories);

        if ($this->cacheJsonSchema) {
            $this->cache->set($cacheKey, $openApi);
        }

        return $openApi;
    }

    private function getCacheKey(array $directories): string
    {
        return \md5(\var_export([self::class, $directories], true));
    }

    public function withCache(): self
    {
        $new = clone $this;
        $new->cacheJsonSchema = true;
        return $new;
    }
}
