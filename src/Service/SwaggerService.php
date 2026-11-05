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
use function is_array;

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

        // `aliases` and `namespaces` are docblock-annotation features forwarded through the Generator
        // options on both supported major versions of zircote/swagger-php; on both majors the only
        // entry point is `Generator::setAliases()`/`Generator::setNamespaces()` (deprecated docblock-only
        // in 6.x since 6.11, to be removed in 8.0), so the deprecation is suppressed at the call sites.
        // The option arrays are passed through unchanged (same runtime behaviour as before); the
        // precise option types are declared to match the `Generator` option contract.
        if (!empty($this->options['aliases']) && is_array($this->options['aliases'])) {
            /**
             * @var array<string, string> $aliases
             */
            $aliases = $this->options['aliases'];
            /**
             * @psalm-suppress DeprecatedMethod
             */
            $generator->setAliases($aliases);
        }
        if (!empty($this->options['namespaces']) && is_array($this->options['namespaces'])) {
            /**
             * @var list<string> $namespaces
             */
            $namespaces = $this->options['namespaces'];
            /**
             * @psalm-suppress DeprecatedMethod
             */
            $generator->setNamespaces($namespaces);
        }
        if (!empty($this->options['analyser']) && $this->options['analyser'] instanceof AnalyserInterface) {
            $generator->setAnalyser($this->options['analyser']);
        }

        return $generator;
    }
}
