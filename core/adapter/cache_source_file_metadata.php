<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class cache_source_file_metadata
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function size(string $path): int|false
    {
        return filesize($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }
}
