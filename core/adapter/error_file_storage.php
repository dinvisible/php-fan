<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class error_file_storage
{
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function realPath(string $path): string|false
    {
        return realpath($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function write(string $path, string $content): int|false
    {
        return file_put_contents($path, $content);
    }

    /**
     * @return list<string>|false
     */
    public function scanDirectory(string $path): array|false
    {
        return scandir($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function changeMode(string $path, int $mode): bool
    {
        return chmod($path, $mode);
    }
}
