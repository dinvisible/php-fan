<?php

declare(strict_types=1);

use fan\core\adapter\file_system_storage;
use PHPUnit\Framework\TestCase;

final class FileSystemStorageTest extends TestCase
{
    public function testStorageWrapsFileSystemReadOperations(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_file_system_storage_');
        $this->assertIsString($file);
        file_put_contents($file, "alpha\n");
        $storage = new file_system_storage();

        try {
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
            $handle = $storage->openRead($file);
            $this->assertSame("alpha\n", $storage->read($handle, 32));
            $this->assertSame('', $storage->read($handle, 1));
            $this->assertTrue($storage->isEnd($handle));
            $this->assertTrue($storage->close($handle));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
