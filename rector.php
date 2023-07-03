<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/src']);

    // define sets of rules
    $rectorConfig->sets([
//        LevelSetList::UP_TO_PHP_81,
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
//        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);
};
