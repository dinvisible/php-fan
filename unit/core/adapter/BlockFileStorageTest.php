<?php

declare(strict_types=1);

use fan\core\adapter\block_file_storage;
use PHPUnit\Framework\TestCase;

final class BlockFileStorageTest extends TestCase
{
    public function testStorageWrapsBlockFilesystemOperations(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_block_file_storage_');
        $this->assertIsString($file);
        $storage = new block_file_storage();

        try {
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->exists($file));
            $this->assertFalse($storage->exists($file . '.missing'));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
