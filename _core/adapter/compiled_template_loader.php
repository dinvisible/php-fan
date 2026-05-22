<?php

declare(strict_types=1);

namespace fan\core\adapter;

class compiled_template_loader
{
    private static array $paths = [];
    private static bool $registered = false;

    public static function load(string $className, string $path): bool
    {
        self::$paths[ltrim($className, '\\')] = $path;
        self::register();

        return class_exists($className, true);
    }

    private static function register(): void
    {
        if (self::$registered) {
            return;
        }

        spl_autoload_register(static function (string $class): void {
            $path = self::$paths[ltrim($class, '\\')] ?? null;
            if (is_string($path) && is_readable($path)) {
                require_once $path;
            }
        });
        self::$registered = true;
    }
}
