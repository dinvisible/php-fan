<?php

declare(strict_types=1);

namespace fan\core\adapter;

class php_array_file
{
    /**
     * Loads a PHP file that returns structured data, usually an array.
     */
    public static function load(string $path, mixed $default = null): mixed
    {
        if (!is_readable($path)) {
            return $default;
        }

        return self::includeFile($path);
    }

    /**
     * Isolates include scope for legacy PHP-array storage files.
     */
    private static function includeFile(string $path): mixed
    {
        return include $path;
    }
}
