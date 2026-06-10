<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class obfuscator_file_storage
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function makeDirectory(string $path, int $mode = 0750, bool $recursive = true): bool
    {
        return mkdir($path, $mode, $recursive);
    }

    public function size(string $path): int|false
    {
        return filesize($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }

    public function write(string $path, string $content): int|false
    {
        return file_put_contents($path, $content);
    }
}
