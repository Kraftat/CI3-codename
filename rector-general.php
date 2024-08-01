<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    // Set the PHP version you are using
    $rectorConfig->phpVersion(80100); // PHP 8.1

    // Register sets from Rector
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
    ]);

    // Paths to refactor; if none are provided, it defaults to include everything in the app and tests directory
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ]);

    // Optionally you can skip rules or paths
    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/public',
    ]);
};
