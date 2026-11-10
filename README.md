<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Swagger</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii-swagger/v)](https://packagist.org/packages/yiisoft/yii-swagger)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii-swagger/downloads)](https://packagist.org/packages/yiisoft/yii-swagger)
[![Build status](https://github.com/yiisoft/yii-swagger/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/yii-swagger/actions/workflows/build.yml)
[![Code Coverage](https://codecov.io/gh/yiisoft/yii-swagger/graph/badge.svg?token=K86U931ZRK)](https://codecov.io/gh/yiisoft/yii-swagger)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fyii-swagger%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/yii-swagger/master)
[![static analysis](https://github.com/yiisoft/yii-swagger/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/yii-swagger/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/yii-swagger/coverage.svg)](https://shepherd.dev/github/yiisoft/yii-swagger)

OpenAPI Swagger for Yii Framework.

## Requirements

- PHP 8.1 - 8.5.
- swagger-php 5, 6 or 7. Version 7 requires PHP 8.2 or later.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/yii-swagger
```

## Configuration

### 1. Add route configuration to `config/routes.php`

```php
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsHtml;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Yii\Swagger\Action\SwaggerUi;
use Yiisoft\Yii\Swagger\Action\SwaggerJson;

// Swagger routes
Group::create('/swagger', [
    Route::get('')
        ->middleware(FormatDataResponseAsHtml::class)
        ->action(fn (SwaggerUi $swaggerUi) => $swaggerUi->withJsonUrl('/swagger/json-url')),
    Route::get('/json-url')
        ->middleware(FormatDataResponseAsJson::class)
        ->action(SwaggerJson::class),
]),
```

### 2. Add attributes to default API controller

```php
use OpenApi\Attributes as OA;

#[OA\Info(title:"My first API", version:"1.0")]
class DefaultController {
    // ...
}
```

and before actions

```php
use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/endpoint",
    summary: "Get default endpoint",
    responses: [
        new OA\Response(response: "200", description: "Get default action"),
    ],
)]
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    // ...
}
```

See [Swagger-PHP documentation](https://zircote.github.io/swagger-php/guide/attributes.html) for details
on how to annotate your code.

### 3. Configure `SwaggerJson` action

For attributes to be registered you need to configure `SwaggerJson`.

You can use the parameters in `config/params.php` to configure `SwaggerJson`:

```php
//...
'yiisoft/yii-swagger' => [
    'source-paths' => [
        '@src/Controller' // Directory where annotations are used
    ],
    'cacheTTL' => 60 // Enables caching and sets TTL, "null" value means infinite cache TTL.
],
//...
```

### 4. (Optional) Add config for aliases and asset manager

```php
use Yiisoft\Definitions\Reference;
use Yiisoft\Assets\AssetManager;

return [
    //...
    'yiisoft/aliases' => [
        'aliases' => [
            //...
            '@views' => '@root/views',
            '@assets' => '@public/assets',
            '@assetsUrl' => '@baseUrl/assets',
        ],
    ],
    'yiisoft/view' => [
        'basePath' => '@views',
        'defaultParameters' => [
            'assetManager' => Reference::to(AssetManager::class),
        ]
    ],
    //...
];
```

### 5. (Optional) Configure `SwaggerUi` action

You can use the parameters in `config/params.php` to configure `SwaggerUi`.

For example, you can enable persisting authorization by setting the `persistAuthorization` parameter to `true`.

```php
//...
'yiisoft/yii-swagger' => [
    'ui-params' => [
        'persistAuthorization' => true,
    ],
],
//...
```

You can find a complete list of parameters by [following the link](https://swagger.io/docs/open-source-tools/swagger-ui/usage/configuration/).

### 6. (Optional) Configure `SwaggerService`

Use `OpenApi\Attributes` to describe your API. `SwaggerService` uses the classic `OpenApi\Generator` pipeline on
all supported swagger-php versions, including version 7. This keeps the default OpenAPI 3.0.0 output, unreferenced
components, generator options and injected generators working as before. The `OpenApi\Spec` attributes and
`OpenApi\Builder` pipeline are not supported by this service.

You can specify options for generation an `OpenApi\Annotations\OpenApi`
instance in `config/params.php` to configure `SwaggerService`:

```php
//...
'yiisoft/yii-swagger' => [
    // Default values are specified.
    'options' => [
        'aliases' => [],
        'namespaces' => [],
        'config' => [],
        'validate' => true,
        'version' => OpenApi\Annotations\OpenApi::DEFAULT_VERSION,
    ],
],
//...
```

Or you can specify definition of `OpenApi\Generator` in `di-web.php`:

```php
use Yiisoft\Yii\Swagger\Service\SwaggerService;

return [
    Generator::class => [
        'setAnalyser' => [myAnalyser()],
    ],
    //...
]
```

For more information about generation an `OpenApi\Annotations\OpenApi` instance, see the
documentation of the [zircote/swagger-php](https://github.com/zircote/swagger-php) package.

### 7. (Optional) Use route paths in OpenAPI

You can omit an operation's `path` and use its Yii route instead. Keep your normal Swagger source-path configuration.
This integration needs `yiisoft/router` `^4.0.2` and a compatible router implementation. Invokable and PSR-15
request-handler class strings also need `yiisoft/middleware-dispatcher` `^5.2`.

Add this entry to your application's `config/di-web.php`:

```php
use OpenApi\Generator;
use Yiisoft\Yii\Swagger\Factory\RoutePathGeneratorFactory;

return [
    Generator::class => RoutePathGeneratorFactory::create(...),
];
```

For example, register `Route::get('/posts')->action([PostController::class, 'index'])` and omit `path` from the
`OA\Get` attribute on `PostController::index()`. The generated GET path is `/posts`. Changing that route changes
the generated path. Continue describing responses and parameters in your attributes; explicit paths stay unchanged.

Use canonical class and method names and simple literal or `{parameter}` paths. Unsupported or ambiguous routes
need an explicit OpenAPI path. See [supported routes and custom generators](docs/route-paths.md) for the complete
matching rules and an example with path parameters.

After changing routes or attributes, generate from a fresh source scan and clear the Swagger document cache.
Discard a previously processed or failed `Analysis` before retrying with changed inputs; inferred paths and partial
mutations are not recomputed or rolled back.

Without this opt-in definition, normal generation works as before and does not require Router.

## Documentation

- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Swagger is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
