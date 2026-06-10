<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class template_file_storage
{
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }

    public function write(string $path, string $data): int|false
    {
        return file_put_contents($path, $data);
    }
}
