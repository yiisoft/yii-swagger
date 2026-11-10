# Route-derived OpenAPI paths

The [README quick start](../README.md#7-optional-use-route-paths-in-openapi) enables this integration with a generator factory.
This page describes custom generators, path parameters and the supported route patterns.

## Custom generators

Install `yiisoft/router` version `^4.0.2` and a compatible router implementation in your application.
The router is optional: normal attribute-based generation works without it.
Invokable and PSR-15 request-handler class strings require `yiisoft/middleware-dispatcher` version `^5.2`.

To use your registered routes as the source of operation paths, insert `RoutePath` before the classic
swagger-php `BuildPaths` processor. Pass your application's resolved route collection and inject the generator
into `SwaggerService`. For example, add this definition to `config/di-web.php`:

```php
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Yii\Swagger\Processor\RoutePath;

return [
    Generator::class => static function (RouteCollectionInterface $routes): Generator {
        $generator = new Generator();
        $generator->getProcessorPipeline()->insert(new RoutePath($routes), BuildPaths::class);
        return $generator;
    },
];
```

The factory creates a new generator on each call. You can customize the returned generator before injecting it.
If your application already defines a custom `Generator`, keep that definition and insert `RoutePath` there as above;
do not replace your custom analyser, options or processor pipeline with a second generator definition.

As with any injected generator, configure generator-construction options (version, config, aliases, namespaces and
analyser) on the generator itself. `SwaggerService` applies those options from `withOptions()` only when it creates
its own generator. Analysis and validation options are still forwarded to an injected generator.

## Path parameters

Keep operation attributes on the action method and omit `path` for operations that should follow routing.
For example, a route registered as:

```php
Group::create('/api')->routes(
    Route::get('/posts/{id}')->action([PostController::class, 'view']),
)
```

can use this attribute on `PostController::view()`:

```php
#[OA\Get(
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [new OA\Response(response: '200', description: 'Post details')],
)]
```

The generated GET path is `/api/posts/{id}`. Changing the route changes the generated path without editing
the attribute. Continue describing parameters, responses and other API details in the attributes; the processor
does not infer their schemas. After changing routes or attributes, regenerate from the source files and clear the
Swagger document cache. A previously processed `Analysis` already contains generated paths; use a fresh source scan
instead of reusing it when routes change. A failed generation can also leave partial mutations; discard that
`Analysis` before retrying with changed inputs. There is no rollback or recomputation of previously inferred paths.

## Supported routes

Matching has these boundaries:

- Only the **last enabled handler definition** of each resolved route is considered. Use a class string for an
  invokable action (`__invoke()`) or a PSR-15 request handler (`handle()`), or `[ClassName::class, 'method']` for
  a controller action. Matching requires the exact declaring class, method and HTTP method. Use the method's
  declared casing, as required by the dispatcher for controller arrays. Earlier middleware,
  closures, object instances, array configuration definitions, container aliases and inheritance-based action
  resolution are not inferred. A class string implementing PSR-15 `MiddlewareInterface` is unsupported, even if
  it also has `__invoke()` or `handle()`: the dispatcher executes its `process()` method instead.
- Matching concerns the final **enabled** definition, not whether it was registered with `action()`.
  Disabled definitions are excluded by the route collection. If disabling the final definition makes a previous
  definition terminal, only that remaining final definition is eligible. An unsupported final definition never
  causes a search through earlier middleware; supply an explicit OpenAPI path for such a pipeline.
- Paths include resolved group prefixes. Supported patterns start with `/` and contain URI literal characters
  or `{parameter}` placeholders. Literal `+` and percent bytes are unsupported because router implementations
  can decode request paths differently; supply an explicit OpenAPI path matching the actual request URL.
  Parameter names must start with a letter or underscore,
  followed by letters, digits or underscores. Regex placeholders, optional route segments, query strings and
  fragments are not converted. Route hosts and locale middleware do not add OpenAPI server declarations.
- An explicit operation path, or an operation nested in an explicit `OA\PathItem`, keeps its normal behavior.
  For an operation without a path, a missing or ambiguous match, unsupported pattern, or absent method context
  throws `InvalidArgumentException` with instructions to correct the route or supply an explicit OpenAPI path.
  Several matching routes are ambiguous even when they share a path.

The processor reads the resolved collection; it does not load route configuration files, instantiate controllers,
execute actions or alter normal generation unless you install it in the generator's pipeline.

