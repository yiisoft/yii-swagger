<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Service;

use InvalidArgumentException;
use OpenApi\Analysers\AnalyserInterface;
use OpenApi\Analysis;
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
        private readonly ?Generator $generator = null,
        private readonly ?LoggerInterface $logger = null,
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
        $analysis = isset($this->options['analysis']) && $this->options['analysis'] instanceof Analysis
            ? $this->options['analysis']
            : null;
        $validate = !isset($this->options['validate']) || (bool) $this->options['validate'];

        $generator = $this->generator ?? $this->createGenerator();

        $openApi = $generator->generate($directories, $analysis, $validate);

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

    private function createGenerator(): Generator
    {
        $config = [];
        if (isset($this->options['config']) && is_array($this->options['config']) && !array_is_list($this->options['config'])) {
            /** @var array<string, mixed> */
            $config = $this->options['config'];
        }

        $generator = (new Generator($this->logger))
            ->setVersion(isset($this->options['version']) ? (string) $this->options['version'] : null)
            ->setConfig($config);

        if (!empty($this->options['aliases']) && is_array($this->options['aliases'])) {
            $generator->setAliases($this->options['aliases']);
        }
        if (!empty($this->options['namespaces']) && is_array($this->options['namespaces'])) {
            $generator->setNamespaces($this->options['namespaces']);
        }
        if (!empty($this->options['analyser']) && $this->options['analyser'] instanceof AnalyserInterface) {
            $generator->setAnalyser($this->options['analyser']);
        }

        return $generator;
    }
}
