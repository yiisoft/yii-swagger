# Yii Swagger Change Log

## 2.1.0 under development

- Chg #79: Add `\Yiisoft\Swagger\Action\SwaggerJson` and `\Yiisoft\Swagger\Action\SwaggerUi` actions,
  mark `\Yiisoft\Swagger\Middleware\SwaggerJson` and `\Yiisoft\Swagger\Middleware\SwaggerUi` as deprecated. 
  It will be removed in next major version. (@xepozz)

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
