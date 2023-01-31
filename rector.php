<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74,
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_74);
    $rectorConfig->paths([
        __DIR__ .'/lib',
        __DIR__ .'/tests',
    ]);
    $rectorConfig->skip([
        // Shorthand Elvis rule is not allowed by strict phpstan
        TernaryToElvisRector::class,
        // We have to skip this rule as it messes up the Mock objects sometimes
        TypedPropertyFromAssignsRector::class => [
            __DIR__ . '/tests',
        ],
    ]);
};
