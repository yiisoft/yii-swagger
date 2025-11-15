<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use InvalidArgumentException;
use OpenApi\Generator;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Util;
use OpenApi\Annotations\OpenApi;
use RuntimeException;
use Yiisoft\Aliases\Aliases;

use function array_map;
use function dirname;
use function sprintf;

final class SwaggerService
{
    private string $viewPath;
    private string $viewName = 'swagger-ui';
    private array $options = [];

    public function __construct(
        private readonly Aliases $aliases,
    ) {
        $this->viewPath = dirname(__DIR__, 2) . '/views';
    }

    public function getViewPath(): string
    {
        return $this->aliases->get($this->viewPath);
    }

    public function getViewName(): string
    {
        return $this->viewName;
    }

    /**
     * Returns a new instance with the specified options for {@see OpenApi} generation.
     *
     * @param array $options For {@see Generator::scan()}.
     */
    public function withOptions(array $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    public function fetch(array $annotationPaths): OpenApi
    {
        if ($annotationPaths === []) {
            throw new InvalidArgumentException('Annotation paths cannot be empty array.');
        }

        $directories = array_map($this->aliases->get(...), $annotationPaths);
        $openApi = Generator::scan(Util::finder($directories), $this->options);

        if ($openApi === null) {
            throw new RuntimeException(sprintf(
                'No OpenApi target set. Run the "%s" processor before "%s::fetch()".',
                MergeIntoOpenApi::class,
                self::class,
            ));
        }

        return $openApi;
    }
}
