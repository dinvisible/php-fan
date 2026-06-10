<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class image_output_writer
{
    public function write(mixed $image, ?string $type, ?string $path = null, int|float $quality = 80): bool
    {
        return match ($type) {
            'jpeg', 'jpg' => imagejpeg($image, $path, (int)$quality),
            'png' => imagepng($image, $path, (int)$this->pngQuality($quality)),
            'gif' => imagegif($image, $path),
            'wbmp' => imagewbmp($image, $path),
            'xbm' => imagexbm($image, $path),
            default => false,
        };
    }

    private function pngQuality(int|float $quality): int|float
    {
        return $quality > 10 ? round($quality / 10) : $quality;
    }
}
