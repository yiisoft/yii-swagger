<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://github.com/yiisoft.png" height="100px">
    </a>
    <h1 align="center">Yii Swagger</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii-swagger/v/stable.png)](https://packagist.org/packages/yiisoft/yii-swagger)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii-swagger/downloads.png)](https://packagist.org/packages/yiisoft/yii-swagger)
[![Build status](https://github.com/yiisoft/yii-swagger/workflows/build/badge.svg)](https://github.com/yiisoft/yii-swagger/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/yii-swagger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-swagger/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/yii-swagger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-swagger/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2FSwagger%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/yii-swagger/master)
[![static analysis](https://github.com/yiisoft/yii-swagger/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/yii-swagger/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/yii-swagger/coverage.svg)](https://shepherd.dev/github/yiisoft/yii-swagger)

## Installation

The package could be installed with composer:

```
composer require yiisoft/yii-swagger
```

## Configuration

### 1. Add route configuration to `config/routes.php`

```php
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsHtml;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Middleware\SwaggerJson;

// Swagger routes
Group::create('/swagger', [
    Route::get('')
        ->addMiddleware(fn (SwaggerUi $swaggerUi) => $swaggerUi->withJsonUrl('/swagger/json-url'))
        ->addMiddleware(FormatDataResponseAsHtml::class),
    Route::get('/json-url')
        ->addMiddleware(static function (SwaggerJson $swaggerJson) {
            return $swaggerJson
                // Uncomment cache for production environment
                // ->withCache(3600)
                ->withAnnotationPaths([
                    '@src/Controller' // Path to API controllers
                ]);
        })
        ->addMiddleware(FormatDataResponseAsJson::class),
]),

``` 

### 2. Add annotations to default API controller

```php
/**
 * @OA\Info(title="My first API", version="1.0")
 */
class DefaultController {
    // ...
}
```

and before actions

```php
/**
 * @OA\Get(
 *     path="/api/endpoint",
 *     @OA\Response(response="200", description="Get default action")
 * )
 */
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    // ...
}
```

### 3. (Optional) Add config for aliases and asset manager

```php
use Yiisoft\Factory\Definitions\Reference;
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
```

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```shell
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Yii Swagger is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
