<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class meta_file_storage
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}
