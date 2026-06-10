<?php

declare(strict_types=1);

use fan\core\adapter\error_logger_file_storage;
use PHPUnit\Framework\TestCase;

final class ErrorLoggerFileStorageTest extends TestCase
{
    public function testStorageWrapsErrorLoggerFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_error_logger_file_storage_' . bin2hex(random_bytes(4));
        mkdir($dir);
        $file = $dir . '/error.log';
        file_put_contents($file, 'payload');
        $storage = new error_logger_file_storage();

        try {
            $this->assertTrue($storage->isDirectory($dir));
            $this->assertTrue($storage->isWritable($dir));
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isWritable($file));
            $this->assertFalse($storage->exists($file . '.missing'));
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
