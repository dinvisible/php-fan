<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class file_data_storage
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isLink(string $path): bool
    {
        return is_link($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function readLink(string $path): string|false
    {
        return readlink($path);
    }

    public function makeDirectory(string $path): bool
    {
        return mkdir($path);
    }

    public function size(string $path): int|false
    {
        return filesize($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }

    public function moveUploadedFile(string $sourcePath, string $targetPath): bool
    {
        return move_uploaded_file($sourcePath, $targetPath);
    }

    public function write(string $path, string $data): int|false
    {
        return file_put_contents($path, $data);
    }

    public function rename(string $sourcePath, string $targetPath): bool
    {
        return rename($sourcePath, $targetPath);
    }

    public function copy(string $sourcePath, string $targetPath): bool
    {
        return copy($sourcePath, $targetPath);
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function clearStatCache(): void
    {
        clearstatcache();
    }
}
