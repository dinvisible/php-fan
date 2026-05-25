<?php

declare(strict_types=1);

$phpFanRoot = dirname(__DIR__);

spl_autoload_register(static function (string $class) use ($phpFanRoot): void {
    if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
        return;
    }

    $bootstrapApplicationRoots = [
        'fan\\core\\bootstrap\\' => $phpFanRoot . '/_core/application/',
    ];

    foreach ($bootstrapApplicationRoots as $prefix => $root) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        if (str_contains($relativeClass, 'factory')) {
            break;
        }

        $file = $root . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_readable($file)) {
            require_once $file;
        }

        return;
    }

    $factoryRoots = [
        'fan\\core\\adapter\\' => $phpFanRoot . '/_core/factory/adapter/',
        'fan\\core\\bootstrap\\' => $phpFanRoot . '/_core/factory/',
        'fan\\core\\di\\' => $phpFanRoot . '/_core/factory/',
        'fan\\core\\runtime\\' => $phpFanRoot . '/_core/factory/runtime/',
    ];

    foreach ($factoryRoots as $prefix => $root) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        if (!str_contains($relativeClass, 'factory')) {
            return;
        }

        $file = $root . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_readable($file)) {
            require_once $file;
        }

        return;
    }
});

spl_autoload_register(static function (string $class): void {
    $projectPrefix = 'fan\\project\\';
    if (!str_starts_with($class, $projectPrefix)) {
        return;
    }

    if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
        return;
    }

    $coreClass = 'fan\\core\\' . substr($class, strlen($projectPrefix));
    if (!class_exists($coreClass) && !interface_exists($coreClass) && !trait_exists($coreClass)) {
        return;
    }

    class_alias($coreClass, $class, false);
});
