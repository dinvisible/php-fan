<?php

declare(strict_types=1);

use fan\core\adapter\image_canvas_operations;
use PHPUnit\Framework\TestCase;

final class ImageCanvasOperationsTest extends TestCase
{
    public function testOperationsCreateCanvasAndCopyPixels(): void
    {
        $operations = new image_canvas_operations();
        $source = $operations->createTrueColor(2, 2);
        $destination = $operations->createTrueColor(4, 4);
        $color = $operations->colorAllocate($source, 10, 20, 30);

        $this->assertInstanceOf(GdImage::class, $source);
        $this->assertInstanceOf(GdImage::class, $destination);
        $this->assertIsInt($color);
        $this->assertTrue($operations->fillRectangle($source, 0, 0, 1, 1, $color));
        $this->assertTrue($operations->copyResampled($destination, $source, 0, 0, 0, 0, 4, 4, 2, 2));
        $this->assertSame(4, $operations->width($destination));
        $this->assertSame(4, $operations->height($destination));
    }

    public function testOperationsWrapFiltersRotationTransparencyAndMerge(): void
    {
        $operations = new image_canvas_operations();
        $source = $operations->createTrueColor(2, 2);
        $destination = $operations->createTrueColor(4, 4);
        $color = $operations->colorAllocate($source, 255, 255, 255);

        $this->assertIsInt($color);
        $this->assertIsInt($operations->colorTransparent($source, $color));
        $this->assertTrue($operations->filter($source, IMG_FILTER_GRAYSCALE));
        $this->assertInstanceOf(GdImage::class, $operations->rotate($source, 90.0, $color));
        $this->assertTrue($operations->copyMerge($destination, $source, 0, 0, 0, 0, 2, 2, 50));
    }

    public function testSourceOwnsNativeCanvasOperationsBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/image_canvas_operations.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class image_canvas_operations', $source);
        $this->assertStringContainsString('imagecreatetruecolor($width, $height)', $source);
        $this->assertStringContainsString('imagecopyresampled(', $source);
        $this->assertStringContainsString('imagecopymerge(', $source);
        $this->assertStringContainsString('imagefilter($image, $filter, ...$arguments)', $source);
        $this->assertStringContainsString('imagestring($image, $font, $x, $y, $string, $color)', $source);
        $this->assertStringContainsString('imageline($image, $x1, $y1, $x2, $y2, $color)', $source);
        $this->assertStringContainsString('imagefontwidth($font)', $source);
    }
}
