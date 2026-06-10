<?php

declare(strict_types=1);

use fan\core\adapter\meta_file_storage;
use PHPUnit\Framework\TestCase;

final class MetaFileStorageTest extends TestCase
{
    public function testStorageWrapsMetaFilesystemOperations(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_meta_file_storage_');
        $this->assertIsString($file);
        $storage = new meta_file_storage();

        try {
            $this->assertTrue($storage->exists($file));
            $this->assertFalse($storage->exists($file . '.missing'));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
