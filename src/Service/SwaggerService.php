<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use InvalidArgumentException;
use function array_map;
use OpenApi\Annotations\OpenApi;

use function OpenApi\scan;
use Yiisoft\Aliases\Aliases;

final class SwaggerService
{
    private Aliases $aliases;

    private string $viewPath;
    private string $viewName;

    public function __construct(Aliases $aliases)
    {
        $this->aliases = $aliases;
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
        if (count($annotationPaths) === 0) {
            throw new InvalidArgumentException('$annotationPaths cannot be empty array');
        }

        $directories = array_map(fn (string $path) => $this->aliases->get($path), $annotationPaths);
        return scan($directories);
    }
}
