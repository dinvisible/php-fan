<?php

declare(strict_types=1);

function php_fan_composer_autoload_symbol_exists(string $symbol, bool $autoload = false): bool
{
    $symbolExists = static fn(string $name, bool $autoloadFlag): bool => class_exists($name, $autoloadFlag)
        || interface_exists($name, $autoloadFlag)
        || trait_exists($name, $autoloadFlag);

    return $symbolExists($symbol, $autoload);
}

function php_fan_composer_autoload_path_for(string $class): ?string
{
    $root = dirname(__DIR__);
    $prefixRoots = [
        'fan\\core\\adapter\\' => [
            $root . '/core/adapter',
            $root . '/core/factory/adapter',
        ],
        'fan\\core\\bootstrap\\' => [
            $root . '/core/application',
        ],
        'fan\\core\\di\\' => [
            $root . '/core/di',
            $root . '/core/factory',
        ],
        'fan\\core\\runtime\\' => [
            $root . '/core/runtime',
            $root . '/core/factory/runtime',
        ],
    ];

    foreach ($prefixRoots as $prefix => $roots) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relativePath = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        foreach ($roots as $directory) {
            $path = $directory . '/' . $relativePath;
            if (is_file($path)) {
                return $path;
            }
        }
    }

    return null;
}

spl_autoload_register(static function (string $class): void {
    if (php_fan_composer_autoload_symbol_exists($class, false)) {
        return;
    }

    $path = php_fan_composer_autoload_path_for($class);
    if ($path === null) {
        return;
    }

    require_once $path;
});

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
