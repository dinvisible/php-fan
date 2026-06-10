<?php

declare(strict_types=1);

use fan\core\adapter\root_html_file_storage;
use PHPUnit\Framework\TestCase;

final class RootHtmlFileStorageTest extends TestCase
{
    public function testStorageWrapsRootHtmlFilesystemOperations(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_root_html_file_storage_');
        $this->assertIsString($file);
        file_put_contents($file, 'payload');
        $storage = new root_html_file_storage();

        try {
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
            $this->assertSame('payload', $storage->read($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
