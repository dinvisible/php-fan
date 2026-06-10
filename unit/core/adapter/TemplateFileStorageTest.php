<?php

declare(strict_types=1);

use fan\core\adapter\template_file_storage;
use PHPUnit\Framework\TestCase;

final class TemplateFileStorageTest extends TestCase
{
    public function testStorageWrapsTemplateFilesystemOperations(): void
    {
        $file = sys_get_temp_dir() . '/fan_template_file_storage_' . bin2hex(random_bytes(4)) . '.tpl';
        $storage = new template_file_storage();

        try {
            $this->assertFalse($storage->exists($file));
            $this->assertSame(7, $storage->write($file, 'payload'));
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isReadable($file));
            $this->assertIsInt($storage->modifiedTime($file));
            $this->assertSame('payload', $storage->read($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
