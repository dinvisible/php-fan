<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class file_system_storage
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function openRead(string $path): mixed
    {
        return fopen($path, 'r');
    }

    public function read(mixed $handle, int $length): string|false
    {
        return fread($handle, $length);
    }

    public function isEnd(mixed $handle): bool
    {
        return feof($handle);
    }

    public function close(mixed $handle): bool
    {
        return fclose($handle);
    }
}
