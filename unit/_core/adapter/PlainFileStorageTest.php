<?php

declare(strict_types=1);

use fan\core\adapter\plain_file_storage;
use PHPUnit\Framework\TestCase;

final class PlainFileStorageTest extends TestCase
{
    public function testStorageWrapsPlainFileFilesystemOperations(): void
    {
        $baseDir = sys_get_temp_dir() . '/fan_plain_file_storage_' . uniqid('', true);
        $file = $baseDir . '/image.gif';
        $storage = new plain_file_storage();

        try {
            $this->assertTrue($storage->makeDirectory($baseDir, 0777, true));
            $this->assertTrue($storage->isDirectory($baseDir));
            $this->assertTrue($storage->isWritable($baseDir));
            file_put_contents($file, 'content');

            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
            $this->assertSame(7, $storage->size($file));
            $this->assertIsInt($storage->modifiedTime($file));

            ob_start();
            $this->assertSame(7, $storage->outputFile($file));
            $this->assertSame('content', ob_get_clean());

            $stream = fopen('php://temp', 'r+');
            $this->assertIsResource($stream);
            fwrite($stream, 'stream-content');
            ob_start();
            $this->assertTrue($storage->rewindStream($stream));
            $this->assertSame(14, $storage->passThroughStream($stream));
            $this->assertSame('stream-content', ob_get_clean());
            fclose($stream);
        } finally {
            if (is_file($file)) {
                unlink($file);
            }
            if (is_dir($baseDir)) {
                rmdir($baseDir);
            }
        }
    }
}
