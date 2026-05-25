<?php

declare(strict_types=1);

use fan\core\adapter\error_file_storage;
use PHPUnit\Framework\TestCase;

final class ErrorFileStorageTest extends TestCase
{
    public function testStorageWrapsErrorPacketFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_error_file_storage_' . bin2hex(random_bytes(4));
        mkdir($dir);
        $file = $dir . '/packet.log.php';
        $storage = new error_file_storage();

        try {
            $this->assertTrue($storage->isDirectory($dir));
            $this->assertSame(realpath($dir), $storage->realPath($dir));
            $this->assertSame(7, $storage->write($file, 'payload'));
            $this->assertTrue($storage->exists($file));
            $this->assertContains('packet.log.php', $storage->scanDirectory($dir));
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isWritable($file));
            $this->assertTrue($storage->changeMode($file, 0666));
            $this->assertTrue($storage->delete($file));
            $this->assertFalse($storage->exists($file));
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
