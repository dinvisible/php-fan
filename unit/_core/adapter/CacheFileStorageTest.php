<?php

declare(strict_types=1);

use fan\core\adapter\cache_file_storage;
use PHPUnit\Framework\TestCase;

final class CacheFileStorageTest extends TestCase
{
    public function testStorageWrapsCacheFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_cache_file_storage_' . bin2hex(random_bytes(4));
        $file = $dir . '/entry.cache';
        $storage = new cache_file_storage();

        try {
            $this->assertTrue($storage->makeDirectory($dir, 0777, true));
            $this->assertTrue($storage->isDirectory($dir));
            $this->assertTrue($storage->isWritable($dir));
            $this->assertFalse($storage->exists($file));
            $this->assertSame(7, $storage->write($file, 'payload', LOCK_EX));
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isWritable($file));
            $this->assertSame('payload', $storage->read($file));
            $this->assertTrue($storage->delete($file));
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
