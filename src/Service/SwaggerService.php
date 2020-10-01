<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use OpenApi\Annotations\OpenApi;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;

use Yiisoft\Swagger\Exception\InvalidArgumentException;

use function array_map;
use function OpenApi\scan;

final class SwaggerService
{
    private Aliases $aliases;

    private string $viewPath;
    private string $viewName;

    public function __construct(ContainerInterface $container)
    {
        $this->aliases = $container->get(Aliases::class);
        $this->setupDefaults();
    }

    private function setupDefaults(): void
    {
        $this->viewPath = dirname(__DIR__, 2) . '/views';
        $this->viewName = 'swagger-ui';
    }

    public function getViewPath(): string
    {
        return $this->aliases->get($this->viewPath);
    }

    public function getViewName(): string
    {
        return $this->viewName;
    }

    public function fetch(array $annotationPaths): OpenApi
    {
        if(count($annotationPaths) === 0) {
            throw new InvalidArgumentException('$annotationPaths cannot be empty array');
        }

        $directories = array_map(fn(string $path) => $this->aliases->get($path), $annotationPaths);
        return scan($directories);
    }
}
