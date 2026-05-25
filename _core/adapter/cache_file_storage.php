<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class cache_file_storage
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function makeDirectory(string $path, int $mode, bool $recursive = false): bool
    {
        return mkdir($path, $mode, $recursive);
    }

    public function write(string $path, string $data, int $flags = 0): int|false
    {
        return file_put_contents($path, $data, $flags);
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }
}
