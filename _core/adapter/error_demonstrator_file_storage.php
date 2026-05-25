<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class error_demonstrator_file_storage
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }
}
