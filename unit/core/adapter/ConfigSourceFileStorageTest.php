<?php

declare(strict_types=1);

use fan\core\adapter\config_source_file_storage;
use PHPUnit\Framework\TestCase;

final class ConfigSourceFileStorageTest extends TestCase
{
    public function testStorageWrapsConfigSourceExistenceChecks(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_config_source_');
        $this->assertIsString($file);
        $storage = new config_source_file_storage();

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
