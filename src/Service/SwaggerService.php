<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use OpenApi\Annotations\OpenApi;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Swagger\Interfaces\SwaggerServiceInterface;

final class SwaggerService implements SwaggerServiceInterface
{

    private Aliases $aliases;
    private CacheInterface $cache;

    private $isDebug = false;
    private string $viewPath = '@root/vendor/yiisoft/yii-swagger/views';
    private string $viewName = 'swagger-ui';

    public function __construct(ContainerInterface $container)
    {
        $this->aliases = $container->get(Aliases::class);
        $this->cache = $container->get(CacheInterface::class);
    }

    private function getCacheKey(array $directories): string
    {
        return \md5(\var_export([self::class, $directories], true));
    }

    public function getViewPath(): string
    {
        return $this->aliases->get($this->viewPath);
    }

    public function getViewName(): string
    {
        return $this->viewName;
    }

    public function withViewPath(string $viewPath): SwaggerServiceInterface
    {
        $new = clone $this;
        $new->viewPath = $viewPath;
        return $new;
    }

    public function withViewName(string $viewName): SwaggerServiceInterface
    {
        $new = clone $this;
        $new->viewName = $viewName;
        return $new;
    }

    public function fetch(array $annotationPaths): OpenApi
    {
        if ($this->isDebug) {
            $cacheKey = $this->getCacheKey($annotationPaths);
            if ($this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }
        }

        $directories = \array_map(fn(string $path) => $this->aliases->get($path), $annotationPaths);
        $openApi = \OpenApi\scan($directories);

        if ($this->isDebug) {
            $this->cache->set($cacheKey, $openApi);
        }

        return $openApi;
    }

    public function withDebug(): SwaggerServiceInterface
    {
        $new = clone $this;
        $new->isDebug = true;
        return $new;
    }
}
