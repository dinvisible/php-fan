<?php

declare(strict_types=1);

function php_fan_composer_autoload_symbol_exists(string $symbol, bool $autoload = false): bool
{
    $symbolExists = static fn(string $name, bool $autoloadFlag): bool => class_exists($name, $autoloadFlag)
        || interface_exists($name, $autoloadFlag)
        || trait_exists($name, $autoloadFlag);

    return $symbolExists($symbol, $autoload);
}

spl_autoload_register(static function (string $class): void {
    $projectPrefix = 'fan\\project\\';
    if (!str_starts_with($class, $projectPrefix)) {
        return;
    }

    if (php_fan_composer_autoload_symbol_exists($class, false)) {
        return;
    }

    $coreClass = 'fan\\core\\' . substr($class, strlen($projectPrefix));
    if (!php_fan_composer_autoload_symbol_exists($coreClass, true)) {
        return;
    }

    class_alias($coreClass, $class, false);
});
