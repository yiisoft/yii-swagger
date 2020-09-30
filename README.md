<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://github.com/yiisoft.png" height="100px">
    </a>
    <h1 align="center">Yii Swagger</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/swagger/v/stable.png)](https://packagist.org/packages/yiisoft/swagger)
[![Total Downloads](https://poser.pugx.org/yiisoft/swagger/downloads.png)](https://packagist.org/packages/yiisoft/swagger)
[![Build status](https://github.com/yiisoft/swagger/workflows/build/badge.svg)](https://github.com/yiisoft/swagger/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/swagger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/swagger/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/swagger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/swagger/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2FSwagger%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/swagger/master)
[![static analysis](https://github.com/yiisoft/swagger/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/swagger/actions?query=workflow%3A%22static+analysis%22)

## Installation

The package could be installed with composer:

```
composer install yiisoft/swagger
```

## Configuration

##### 1. Add swagger service definition to config/web.php

```php
use Yiisoft\Swagger\Interfaces\SwaggerServiceInterface;
use Yiisoft\Swagger\Service\SwaggerService;

// ...

return [
    // ...
    SwaggerServiceInterface::class => SwaggerService::class,
];
```

*By default SwaggerService is caching JSON schema. If you want to disable caching configure service with debug mode*

```php
SwaggerServiceInterface::class => fn (SwaggerService $swaggerService) => $swaggerService->withDebug()
```

##### 2. Add route configuration to config/routes.php

```php
use Yiisoft\Swagger\Middleware\SwaggerUi;
use Yiisoft\Swagger\Middleware\SwaggerJson;
```

```php
// Swagger routes
Group::create('/swagger', [
    Route::get('')
        ->addMiddleware(fn (SwaggerUi $swaggerUi) => $swaggerUi->withJsonUrl('/swagger/json-url')),
    Route::get('/json-url')
        ->addMiddleware(static function (SwaggerJson $swaggerJson) {
            return $swaggerJson->withAnnotationPaths([
                '@src/Controller' // path to API controllers
            ]);
        })
        ->addMiddleware(FormatDataResponseAsJson::class),
])
``` 

##### 3. Add annotations to default API controller

```php
/**
 * @OA\Info(title="My first API", version="1.0")
 */
class DefaultController {
    // ...
}
```

and bofore actions

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

## Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```php
./vendor/bin/phpunit
```

## Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```php
./vendor/bin/infection
```

## Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```php
./vendor/bin/psalm
```
