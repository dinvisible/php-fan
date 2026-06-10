<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class image_source_file_storage
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

    public function rename(string $sourcePath, string $targetPath): bool
    {
        return rename($sourcePath, $targetPath);
    }
}
