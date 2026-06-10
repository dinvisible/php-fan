<?php

declare(strict_types=1);

use fan\core\adapter\log_file_storage;
use PHPUnit\Framework\TestCase;

final class LogFileStorageTest extends TestCase
{
    public function testStorageWrapsLogFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_log_file_storage_' . bin2hex(random_bytes(4));
        $file = $dir . '/app.log';
        $renamed = $dir . '/app.tmp';
        mkdir($dir);
        $storage = new log_file_storage();

        try {
            $this->assertTrue($storage->exists($dir));
            $this->assertTrue($storage->isDirectory($dir));
            $this->assertSame(7, $storage->putContents($file, 'payload'));
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
            $this->assertTrue($storage->isWritable($file));
            $this->assertSame(7, $storage->size($file));
            $this->assertIsInt($storage->modifiedTime($file));
            $this->assertSame('payload', $storage->getContents($file));

            $stream = $storage->openRead($file);
            $this->assertSame(0, $storage->seek($stream, 0));
            $this->assertSame('payload', $storage->read($stream, 7));
            $this->assertSame('', $storage->read($stream, 1));
            $this->assertTrue($storage->isEnd($stream));
            $this->assertTrue($storage->close($stream));

            $this->assertTrue($storage->rename($file, $renamed));
            $stream = $storage->openWrite($file);
            $this->assertSame(7, $storage->write($stream, 'trimmed'));
            $this->assertTrue($storage->close($stream));
            $this->assertTrue($storage->delete($file));
            $this->assertTrue($storage->delete($renamed));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
            if (file_exists($renamed)) {
                unlink($renamed);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
