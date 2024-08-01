<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app/Controllers',
        __DIR__ . '/app/Libraries',
        __DIR__ . '/app/Models',
        __DIR__ . '/app/Helpers',


        // __DIR__ . '/system',
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_82,
    ]);

    // Add CodeIgniter4 specific rule sets

    $rectorConfig->import(__DIR__ . '/vendor/phpdevsr/rector-codeigniter4/config/sets/codeigniter45.php');

    $rectorConfig->skip([
        __DIR__ . '/app/Views',
        __DIR__ . '/app/Config',
        __DIR__ . '/vendor/rector/rector/vendor/symplify/easy-parallel/src/ValueObject/ParallelProcess.php',
    ]);

    $rectorConfig->phpVersion(Rector\ValueObject\PhpVersion::PHP_82);
    
};