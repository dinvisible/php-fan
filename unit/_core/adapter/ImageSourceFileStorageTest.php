<?php

declare(strict_types=1);

use fan\core\adapter\image_source_file_storage;
use PHPUnit\Framework\TestCase;

final class ImageSourceFileStorageTest extends TestCase
{
    public function testStorageWrapsImageSourceFileChecks(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_image_source_');
        $this->assertIsString($file);
        $storage = new image_source_file_storage();

        try {
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
        } finally {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->assertFalse($storage->exists($file));
        $this->assertFalse($storage->isFile($file));
        $this->assertFalse($storage->isReadable($file));
    }

    public function testStorageWrapsImageSourceRename(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'fan_image_source_rename_');
        $this->assertIsString($source);
        $target = $source . '.renamed';
        $storage = new image_source_file_storage();

        try {
            $this->assertTrue($storage->rename($source, $target));
            $this->assertFalse(is_file($source));
            $this->assertTrue(is_file($target));
        } finally {
            if (is_file($source)) {
                unlink($source);
            }
            if (is_file($target)) {
                unlink($target);
            }
        }
    }
}
