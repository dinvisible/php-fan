<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class tab_alias_file_storage
{
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }
}
