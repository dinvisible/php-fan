<?php

declare(strict_types=1);

use fan\core\adapter\error_demonstrator_file_storage;
use PHPUnit\Framework\TestCase;

final class ErrorDemonstratorFileStorageTest extends TestCase
{
    public function testStorageWrapsErrorDemonstratorTemplateFileOperations(): void
    {
        $file = sys_get_temp_dir() . '/fan_error_demonstrator_file_storage_' . bin2hex(random_bytes(4)) . '.html';
        $storage = new error_demonstrator_file_storage();

        try {
            $this->assertFalse($storage->exists($file));
            $this->assertFalse($storage->isFile($file));
            file_put_contents($file, 'Error {{CODE}}');
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isFile($file));
            $this->assertSame('Error {{CODE}}', $storage->read($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
