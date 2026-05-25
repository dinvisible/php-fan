<?php

declare(strict_types=1);

use fan\core\adapter\obfuscator_file_storage;
use PHPUnit\Framework\TestCase;

final class ObfuscatorFileStorageTest extends TestCase
{
    public function testStorageWrapsObfuscatorFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_obfuscator_file_storage_' . bin2hex(random_bytes(4));
        $file = $dir . '/bundle';
        $storage = new obfuscator_file_storage();

        try {
            $this->assertFalse($storage->isDirectory($dir));
            $this->assertTrue($storage->makeDirectory($dir));
            $this->assertTrue($storage->isDirectory($dir));
            $this->assertSame(7, $storage->write($file, 'payload'));
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
            $this->assertSame(7, $storage->size($file));
            $this->assertIsInt($storage->modifiedTime($file));
            $this->assertSame('payload', $storage->read($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
