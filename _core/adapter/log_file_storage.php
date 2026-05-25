<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class log_file_storage
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

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function size(string $path): int|false
    {
        return filesize($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }

    public function rename(string $from, string $to): bool
    {
        return rename($from, $to);
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function putContents(string $path, string $data): int|false
    {
        return file_put_contents($path, $data);
    }

    public function getContents(string $path): string|false
    {
        return file_get_contents($path);
    }

    public function openRead(string $path): mixed
    {
        return fopen($path, 'r');
    }

    public function openWrite(string $path): mixed
    {
        return fopen($path, 'w');
    }

    public function seek(mixed $stream, int $offset): int
    {
        return fseek($stream, $offset);
    }

    public function read(mixed $stream, int $length): string|false
    {
        return fread($stream, $length);
    }

    public function write(mixed $stream, string $data): int|false
    {
        return fwrite($stream, $data);
    }

    public function isEnd(mixed $stream): bool
    {
        return feof($stream);
    }

    public function close(mixed $stream): bool
    {
        return fclose($stream);
    }
}
