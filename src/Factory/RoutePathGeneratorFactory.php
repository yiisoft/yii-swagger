<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Factory;

use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Yii\Swagger\Processor\RoutePath;

/**
 * Creates a classic swagger-php generator that fills missing operation paths from resolved Yii routes.
 *
 * @psalm-suppress DeprecatedClass This factory integrates with the classic swagger-php 5/6/7 API.
 */
final class RoutePathGeneratorFactory
{
    public static function create(RouteCollectionInterface $routes): Generator
    {
        $generator = new Generator();
        $generator->getProcessorPipeline()->insert(new RoutePath($routes), BuildPaths::class);
        return $generator;
    }
}
