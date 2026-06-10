<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class matcher_route_file_storage
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }
}
