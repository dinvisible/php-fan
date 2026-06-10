<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class entity_file_discovery
{
    /**
     * @return list<string>|false
     */
    public function scanDirectory(string $path): array|false
    {
        return scandir($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}
