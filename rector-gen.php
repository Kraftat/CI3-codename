<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app/Libraries',
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::TYPE_DECLARATION,
        SetList::NAMING,
        SetList::EARLY_RETURN,
        SetList::PRIVATIZATION,
    ]);
};
