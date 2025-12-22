# Yii Swagger Change Log

## 2.2.1 under development

- Enh #112: Bump `yiisoft/assets` to version `5.1.1`, refactor `SwaggerUiAsset` (@vjik)
- Chg #113: Change PHP version constraint to `8.1 - 8.4` (@rustamwin)
- Chg #115: Bump `zircote/swagger-php` to version `^5.0`. Drop version `^4.1` of `swagger-api/swagger-ui` (@rustamwin)
- Chg #116: Change project namespace to `Yiisoft\Yii\Swagger` (@rustamwin)
- Chg #118: Remove deprecated `Yiisoft\Yii\Swagger\Middleware\SwaggerJson` and `Yiisoft\Yii\Swagger\Middleware\SwaggerUi` (@rustamwin)
- Enh #120: Add ability to provide `OpenApi\Generator` to `SwaggerService` via constructor (@rustamwin)
- Chg #120: Remove support of `doctrine/annotations` by default (@rustamwin)
- Enh #121: Add support for PHP version `8.4` (@rustamwin)

## 2.2.0 January 27, 2025

- Enh #109: Add support `yiisoft/assets` version of `^5.0` (@vjik)
- Enh #110: Raise the minimum PHP version to 8.1 and minor refactoring (@vjik)

## 2.1.1 September 04, 2024

- Chg #103: Replace `yiisoft/yii-view` dependency with `yiisoft/yii-view-renderer` (@arogachev)

## 2.1.0 September 04, 2024

- Chg #79: Add `\Yiisoft\Swagger\Action\SwaggerJson` and `\Yiisoft\Swagger\Action\SwaggerUi` actions,
  mark `\Yiisoft\Swagger\Middleware\SwaggerJson` and `\Yiisoft\Swagger\Middleware\SwaggerUi` as deprecated. 
  It will be removed in next major version. (@xepozz)
- Chg #97: Raise required `yiisoft/yii-view` version to `^7.1` (@vjik)
- Enh #81: Add `swagger-api/swagger-ui` of version 5 support (@vjik)
- Enh #95: Add support for `psr/http-message` version `^2.0` (@bautrukevich)

## 2.0.0 February 16, 2023

- Chg #69: Adapt configuration group names to Yii conventions (@vjik)
- Enh #62: Explicitly add transitive dependencies `psr/http-message`, `psr/http-server-handler`, 
  `psr/http-server-middleware`, `yiisoft/arrays` and `yiisoft/html` (@vjik)
- Enh #67, #71: Add support `yiisoft/assets` version of `^3.0|^4.0` (@vjik)
- Enh #72: Add support `yiisoft/aliases` version of `^3.0`, `yiisoft/cache` version of `^3.0`,
  `yiisoft/data-response` version of `^2.0`, `yiisoft/yii-view` version of `^6.0` (@vjik)

## 1.2.2 July 28, 2022

- Chg #59: Add support `yiisoft/cache` version of `^2.0` (@xepozz)

## 1.2.1 July 26, 2022

- Chg #58: Add support `yiisoft/yii-view` version of `^5.0` (@rustamwin)

## 1.2.0 February 10, 2022

- Enh #49: Add ability to configure `OpenApi\Annotations\OpenAPI` generation to
  `Yiisoft\Swagger\Service\SwaggerService` (@devanych)

## 1.1.0 January 14, 2022

- Enh #48: Add ability to configure `Yii\Swagger\Middleware\SwaggerJson` via config params (@rustamwin)

## 1.0.0 December 15, 2021

- Initial release.
