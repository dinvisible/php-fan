<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class plain_file_storage
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function makeDirectory(string $path, int $mode = 0777, bool $recursive = false): bool
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

    public function outputFile(string $path): int|false
    {
        return readfile($path);
    }

    public function rewindStream(mixed $stream): bool
    {
        return rewind($stream);
    }

    public function passThroughStream(mixed $stream): int|false
    {
        return fpassthru($stream);
    }
}
