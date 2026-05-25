<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class project_tool_file_storage
{
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

    public function makeDirectory(string $path, int $mode = 0777, bool $recursive = false): bool
    {
        return mkdir($path, $mode, $recursive);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }

    public function write(string $path, string $content): int|false
    {
        return file_put_contents($path, $content);
    }

    public function copy(string $source, string $destination): bool
    {
        return copy($source, $destination);
    }

    public function scanDirectory(string $path): array|false
    {
        return scandir($path);
    }

    public function realPath(string $path): string|false
    {
        return realpath($path);
    }
}
