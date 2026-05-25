<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class image_canvas_operations
{
    public function createTrueColor(int $width, int $height): mixed
    {
        return imagecreatetruecolor($width, $height);
    }

    public function colorTransparent(mixed $image, int $color): int
    {
        return imagecolortransparent($image, $color);
    }

    public function rotate(mixed $image, float $angle, int $backgroundColor): mixed
    {
        return imagerotate($image, $angle, $backgroundColor);
    }

    public function width(mixed $image): int
    {
        return imagesx($image);
    }

    public function height(mixed $image): int
    {
        return imagesy($image);
    }

    public function copyResampled(
        mixed $destination,
        mixed $source,
        int $destinationX,
        int $destinationY,
        int $sourceX,
        int $sourceY,
        int $destinationWidth,
        int $destinationHeight,
        int $sourceWidth,
        int $sourceHeight
    ): bool {
        return imagecopyresampled(
            $destination,
            $source,
            $destinationX,
            $destinationY,
            $sourceX,
            $sourceY,
            $destinationWidth,
            $destinationHeight,
            $sourceWidth,
            $sourceHeight
        );
    }

    public function fillRectangle(mixed $image, int $x1, int $y1, int $x2, int $y2, int $color): bool
    {
        return imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
    }

    public function string(mixed $image, int $font, int $x, int $y, string $string, int $color): bool
    {
        return imagestring($image, $font, $x, $y, $string, $color);
    }

    public function trueTypeBoundingBox(int $size, float $angle, string $fontFile, string $text, array $options = []): array|false
    {
        return imageftbbox($size, $angle, $fontFile, $text, $options);
    }

    public function trueTypeText(
        mixed $image,
        int $size,
        float $angle,
        int $x,
        int $y,
        int $color,
        string $fontFile,
        string $text
    ): array|false {
        return imagettftext($image, $size, $angle, $x, $y, $color, $fontFile, $text);
    }

    public function rectangle(mixed $image, int $x1, int $y1, int $x2, int $y2, int $color): bool
    {
        return imagerectangle($image, $x1, $y1, $x2, $y2, $color);
    }

    public function polygon(mixed $image, array $points, int $pointCount, int $color): bool
    {
        return imagepolygon($image, $points, $pointCount, $color);
    }

    public function filledPolygon(mixed $image, array $points, int $pointCount, int $color): bool
    {
        return imagefilledpolygon($image, $points, $pointCount, $color);
    }

    public function ellipse(mixed $image, int $centerX, int $centerY, int $width, int $height, int $color): bool
    {
        return imageellipse($image, $centerX, $centerY, $width, $height, $color);
    }

    public function filledEllipse(mixed $image, int $centerX, int $centerY, int $width, int $height, int $color): bool
    {
        return imagefilledellipse($image, $centerX, $centerY, $width, $height, $color);
    }

    public function arc(
        mixed $image,
        int $centerX,
        int $centerY,
        int $width,
        int $height,
        int $startAngle,
        int $endAngle,
        int $color
    ): bool {
        return imagearc($image, $centerX, $centerY, $width, $height, $startAngle, $endAngle, $color);
    }

    public function filledArc(
        mixed $image,
        int $centerX,
        int $centerY,
        int $width,
        int $height,
        int $startAngle,
        int $endAngle,
        int $color,
        int $style
    ): bool {
        return imagefilledarc($image, $centerX, $centerY, $width, $height, $startAngle, $endAngle, $color, $style);
    }

    public function line(mixed $image, int $x1, int $y1, int $x2, int $y2, int $color): bool
    {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }

    public function fontWidth(int $font): int
    {
        return imagefontwidth($font);
    }

    public function fontHeight(int $font): int
    {
        return imagefontheight($font);
    }

    public function filter(mixed $image, int $filter, int ...$arguments): bool
    {
        return imagefilter($image, $filter, ...$arguments);
    }

    public function copyMerge(
        mixed $destination,
        mixed $source,
        int $destinationX,
        int $destinationY,
        int $sourceX,
        int $sourceY,
        int $sourceWidth,
        int $sourceHeight,
        int $opacity
    ): bool {
        return imagecopymerge(
            $destination,
            $source,
            $destinationX,
            $destinationY,
            $sourceX,
            $sourceY,
            $sourceWidth,
            $sourceHeight,
            $opacity
        );
    }

    public function colorAllocate(mixed $image, int $red, int $green, int $blue): int|false
    {
        return imagecolorallocate($image, $red, $green, $blue);
    }
}
