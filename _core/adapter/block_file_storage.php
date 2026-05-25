<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class block_file_storage
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}
