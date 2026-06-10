<?php

declare(strict_types=1);

use fan\core\adapter\file_data_storage;
use PHPUnit\Framework\TestCase;

final class FileDataStorageTest extends TestCase
{
    public function testStorageWrapsFileDataNativeOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_file_data_storage_' . bin2hex(random_bytes(4));
        $source = $dir . '/source.txt';
        $copy = $dir . '/copy.txt';
        $renamed = $dir . '/renamed.txt';

        mkdir($dir, 0777, true);
        file_put_contents($source, 'payload');

        $storage = new file_data_storage();

        try {
            $this->assertTrue($storage->exists($source));
            $this->assertTrue($storage->isFile($source));
            $this->assertTrue($storage->isDirectory($dir));
            $this->assertTrue($storage->isWritable($dir));
            $this->assertSame(7, $storage->size($source));
            $this->assertIsInt($storage->modifiedTime($source));
            $this->assertTrue($storage->copy($source, $copy));
            $this->assertTrue($storage->rename($copy, $renamed));
            $this->assertSame(5, $storage->write($copy, 'fresh'));
            $this->assertTrue($storage->delete($copy));
            $this->assertTrue($storage->delete($renamed));
            $this->assertFalse($storage->moveUploadedFile($source, $copy));
        } finally {
            foreach ([$copy, $renamed, $source] as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
