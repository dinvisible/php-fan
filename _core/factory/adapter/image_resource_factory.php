<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class image_resource_factory
{
    public function type(string $path): int|false
    {
        return exif_imagetype($path);
    }

    public function createFromType(string $path, string $type): mixed
    {
        return match ($type) {
            'gif' => imagecreatefromgif($path),
            'jpeg', 'jpg' => imagecreatefromjpeg($path),
            'png' => imagecreatefrompng($path),
            'wbmp' => imagecreatefromwbmp($path),
            'xbm' => imagecreatefromxbm($path),
            default => false,
        };
    }
}
