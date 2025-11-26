<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use InvalidArgumentException;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use OpenApi\Processors\MergeIntoOpenApi;
use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface|null $logger = null,
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
     * @param array $options For {@see Generator}.
     */
    public function withOptions(array $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    public function fetch(array $paths): OpenApi
    {
        if ($paths === []) {
            throw new InvalidArgumentException('Source paths cannot be empty array.');
        }

        $directories = array_map($this->aliases->get(...), $paths);

        $generator = (new Generator($this->logger))
            ->setVersion($this->options['version'] ?? null)
            ->setConfig($this->options['config'] ?? []);

        if (!empty($this->options['aliases'])) {
            $generator->setAliases($this->options['aliases']);
        }
        if (!empty($this->options['namespaces'])) {
            $generator->setNamespaces($this->options['namespaces']);
        }


        $openApi = $generator->generate($directories, null, $this->options['validate'] ?? true);

        if ($openApi === null) {
            throw new RuntimeException(
                sprintf(
                    'No OpenApi target set. Run the "%s" processor before "%s::fetch()".',
                    MergeIntoOpenApi::class,
                    self::class,
                ),
            );
        }

        return $openApi;
    }
}
