<?php

declare(strict_types=1);

use fan\core\adapter\model_request_file_storage;
use PHPUnit\Framework\TestCase;

final class ModelRequestFileStorageTest extends TestCase
{
    public function testStorageWrapsModelRequestSqlFileOperations(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_model_request_');
        $this->assertIsString($file);
        file_put_contents($file, 'SELECT 1');
        $storage = new model_request_file_storage();

        try {
            $this->assertTrue($storage->exists($file));
            $this->assertSame('SELECT 1', $storage->read($file));
            $this->assertFalse($storage->exists($file . '.missing'));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
