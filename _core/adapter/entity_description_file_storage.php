<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class entity_description_file_storage
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function write(string $path, string $content): int|false
    {
        return file_put_contents($path, $content);
    }
}
