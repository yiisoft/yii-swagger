<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Swagger\Processor;

use InvalidArgumentException;
use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Context;
use OpenApi\Generator;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionMethod;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollectionInterface;

use function array_is_list;
use function array_key_last;
use function class_exists;
use function count;
use function implode;
use function is_a;
use function is_array;
use function is_string;
use function ltrim;
use function method_exists;
use function preg_match;
use function sort;
use function sprintf;
use function strtoupper;

/**
 * Fills missing operation paths from resolved Yii routes. Insert before the classic BuildPaths processor.
 *
 * @psalm-suppress DeprecatedClass This processor integrates with the classic swagger-php 5/6/7 API.
 */
final class RoutePath
{
    public function __construct(private readonly RouteCollectionInterface $routes) {}

    /**
     * @psalm-suppress DeprecatedMethod Generator::isDefault() also supports swagger-php 5.
     */
    public function __invoke(Analysis $analysis): void
    {
        $routes = null;

        foreach ($analysis->unmerged()->getAnnotationsOfType(Operation::class) as $operation) {
            if (!Generator::isDefault($operation->path)) {
                continue;
            }

            $reflector = $this->getActionMethod($operation->_context);
            if ($reflector === null) {
                throw new InvalidArgumentException(
                    'Cannot infer an OpenAPI path without an action method. '
                    . 'Annotate the method or set an explicit path.',
                );
            }

            $action = $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName();
            $method = strtoupper($operation->method);
            $routes ??= $this->indexRoutes();
            $matches = $routes[$action][$method] ?? [];

            if ($matches === []) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot infer a path for %s %s(): no matching terminal route handler. '
                    . 'Use an action class or [class, method] with this HTTP method, or set an explicit OpenAPI path.',
                    $method,
                    $action,
                ));
            }

            if (count($matches) > 1) {
                $names = [];
                foreach ($matches as $route) {
                    $names[] = $route->getData('name');
                }
                sort($names);
                throw new InvalidArgumentException(sprintf(
                    'Cannot infer a path for %s %s(): multiple matching routes (%s). Set an explicit OpenAPI path.',
                    $method,
                    $action,
                    implode(', ', $names),
                ));
            }

            $route = $matches[0];
            $path = $route->getData('pattern');
            $pattern = "#\A/(?:[A-Za-z0-9._~!$&'()*+,;=:@/-]|%[A-Fa-f0-9]{2}"
                . '|\{[A-Za-z_][A-Za-z0-9_]*\})*\z#';
            if (preg_match($pattern, $path) !== 1) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot infer a path from route "%s": unsupported pattern "%s". '
                    . 'Use literal segments and {parameter} placeholders, or set an explicit OpenAPI path.',
                    $route->getData('name'),
                    $path,
                ));
            }

            $operation->path = $path;
        }
    }

    private function getActionMethod(Context $context): ?ReflectionMethod
    {
        $reflector = $context->reflector;
        if ($reflector instanceof ReflectionMethod) {
            return $reflector;
        }

        // Early swagger-php 5 analysers provide method context without a reflector.
        if ($reflector !== null || $context->class === null || $context->method === null) {
            return null;
        }

        $class = ltrim(($context->namespace ?? '') . '\\' . $context->class, '\\');
        if (!class_exists($class, false) || !method_exists($class, $context->method)) {
            return null;
        }

        return new ReflectionMethod($class, $context->method);
    }

    /**
     * @return array<string, array<string, list<Route>>>
     */
    private function indexRoutes(): array
    {
        $result = [];
        foreach ($this->routes->getRoutes() as $route) {
            $handlers = $route->getData('enabledMiddlewares');
            if ($handlers === []) {
                continue;
            }
            $handler = $handlers[array_key_last($handlers)];

            if (is_string($handler)) {
                $class = ltrim($handler, '\\');
                if (is_a($class, MiddlewareInterface::class, true)) {
                    continue;
                }
                $method = is_a($class, RequestHandlerInterface::class, true) ? 'handle' : '__invoke';
                $action = $class . '::' . $method;
            } elseif (is_array($handler)) {
                $action = $this->getControllerAction($handler);
                if ($action === null) {
                    continue;
                }
            } else {
                continue;
            }

            foreach ($route->getData('methods') as $httpMethod) {
                $result[$action][$httpMethod][] = $route;
            }
        }

        return $result;
    }

    private function getControllerAction(array $handler): ?string
    {
        if (
            !array_is_list($handler)
            || count($handler) !== 2
            || !is_string($handler[0])
            || !is_string($handler[1])
        ) {
            return null;
        }

        return ltrim($handler[0], '\\') . '::' . $handler[1];
    }
}
