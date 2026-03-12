<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php85\Rector\Property\AddOverrideAttributeToOverriddenPropertiesRector;
use RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap/app.php',
        __DIR__ . '/bootstrap/providers.php',
        __DIR__ . '/config',
        __DIR__ . '/database/factories',
        __DIR__ . '/database/seeders',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/bootstrap/cache',
        __DIR__ . '/database/migrations',
        AddOverrideAttributeToOverriddenPropertiesRector::class,
    ])
    ->withPhpSets(php85: true)
    ->withSetProviders(LaravelSetProvider::class)
    ->withComposerBased(
        phpunit: true,
        laravel: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
    )
    ->withImportNames(
        removeUnusedImports: true,
    )
    ->withConfiguredRule(
        RemoveDumpDataDeadCodeRector::class,
        [
            'dd', 'dump', 'var_dump',
        ],
    );
