<?php

declare(strict_types=1);

use fan\core\adapter\image_output_writer;
use PHPUnit\Framework\TestCase;

final class ImageOutputWriterTest extends TestCase
{
    public function testWriterSavesPngAndNormalizesLegacyQuality(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan-output-');
        $this->assertIsString($file);
        $image = imagecreatetruecolor(2, 2);
        $writer = new image_output_writer();

        try {
            $this->assertTrue($writer->write($image, 'png', $file, 80));
            $this->assertGreaterThan(0, filesize($file));
        } finally {
            @unlink($file);
        }
    }

    public function testWriterReturnsFalseForUnsupportedType(): void
    {
        $writer = new image_output_writer();

        $this->assertFalse($writer->write(imagecreatetruecolor(1, 1), 'unknown'));
    }

    public function testSourceOwnsNativeImageOutputBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/image_output_writer.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class image_output_writer', $source);
        $this->assertStringContainsString('imagejpeg($image, $path, (int)$quality)', $source);
        $this->assertStringContainsString('imagepng($image, $path, (int)$this->pngQuality($quality))', $source);
        $this->assertStringContainsString('imagegif($image, $path)', $source);
    }
}
