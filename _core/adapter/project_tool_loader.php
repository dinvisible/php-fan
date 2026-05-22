<?php

declare(strict_types=1);

namespace fan\core\adapter;

class project_tool_loader
{
    public static function load(string $className, string $path): bool
    {
        if (class_exists($className)) {
            return true;
        }

        if (!is_readable($path)) {
            return false;
        }

        require_once $path;

        return class_exists($className, false);
    }
}
