# Upgrading Instructions for Yii Swagger

This file contains the upgrade notes. These notes highlight changes that could break your
application when you upgrade the package from one version to another.

> **Important!** The following upgrading instructions are cumulative. That is, if you want
> to upgrade from version A to version C and there is version B between A and C, you need
> to following the instructions for both A and B.

## Upgrade from 2.x

- The namespace has changed from `Yiisoft\Swagger` to `Yiisoft\Yii\Swagger`.
  You should update all usages to the new namespace.
- `Yiisoft\Swagger\Middleware\SwaggerJson` and `Yiisoft\Swagger\Middleware\SwaggerUi` was removed.
  Replace usage of them to `\Yiisoft\Swagger\Action\SwaggerJson` and `\Yiisoft\Swagger\Action\SwaggerUi`.
- Rename configuration parameters: `annotation-paths` to `source-paths`, `open-api-options` to `options`.
- Annotations support has been deprecated. To continue using annotations in your code, install `doctrine/annotations`.
- Added optional `OpenApi\Generator` parameter to `SwaggerService` constructor, update instance creating locations.
