<?php

declare(strict_types=1);

use fan\core\adapter\image_resource_factory;
use PHPUnit\Framework\TestCase;

final class ImageResourceFactoryTest extends TestCase
{
    public function testFactoryReadsImageTypeAndCreatesResourceByType(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan-resource-');
        $this->assertIsString($file);
        $image = imagecreatetruecolor(2, 1);
        imagepng($image, $file);

        $factory = new image_resource_factory();

        try {
            $this->assertSame(IMAGETYPE_PNG, $factory->type($file));
            $resource = $factory->createFromType($file, 'png');
        } finally {
            @unlink($file);
        }

        $this->assertInstanceOf(GdImage::class, $resource);
    }

    public function testFactoryReturnsFalseForUnknownResourceType(): void
    {
        $factory = new image_resource_factory();

        $this->assertFalse($factory->createFromType('/tmp/source.image', 'unknown'));
    }

    public function testSourceOwnsNativeImageResourceBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/adapter/image_resource_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class image_resource_factory', $source);
        $this->assertStringContainsString('exif_imagetype($path)', $source);
        $this->assertStringContainsString('imagecreatefrompng($path)', $source);
    }
}
