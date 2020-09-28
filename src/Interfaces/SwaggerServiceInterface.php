<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Interfaces;

use OpenApi\Annotations\OpenApi;

interface SwaggerServiceInterface
{
    public function fetch(array $annotationPaths): OpenApi;

    public function getViewPath(): string;

    public function getViewName(): string;

    public function withViewPath(string $viewPath): SwaggerServiceInterface;

    public function withViewName(string $viewName): SwaggerServiceInterface;

    public function withDebug(): SwaggerServiceInterface;

}
