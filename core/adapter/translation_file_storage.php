<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class translation_file_storage
{
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function write(string $path, string $data): int|false
    {
        return file_put_contents($path, $data);
    }
}
