<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/views', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // Referenced only via its filesystem path (`@vendor/swagger-api/swagger-ui/dist`) for asset
    // publishing, not via a PHP symbol, so the analyser can't detect the usage.
    ->ignoreErrorsOnPackages(['swagger-api/swagger-ui'], [ErrorType::UNUSED_DEPENDENCY]);
