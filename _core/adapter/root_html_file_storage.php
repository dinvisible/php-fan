<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class root_html_file_storage
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }
}
