<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use InvalidArgumentException;
use OpenApi\Generator;
use OpenApi\Util;
use OpenApi\Annotations\OpenApi;
use Yiisoft\Aliases\Aliases;

use function array_map;
use function dirname;

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
        if ($annotationPaths === []) {
            throw new InvalidArgumentException('Annotation paths cannot be empty array.');
        }

        $directories = array_map(fn (string $path) => $this->aliases->get($path), $annotationPaths);

        return Generator::scan(Util::finder($directories));
    }
}
