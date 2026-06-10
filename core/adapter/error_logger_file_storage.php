<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class error_logger_file_storage
{
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}
