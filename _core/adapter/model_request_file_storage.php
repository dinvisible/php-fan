<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class model_request_file_storage
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }
}
