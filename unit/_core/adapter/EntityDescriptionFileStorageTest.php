<?php

declare(strict_types=1);

use fan\core\adapter\entity_description_file_storage;
use PHPUnit\Framework\TestCase;

final class EntityDescriptionFileStorageTest extends TestCase
{
    public function testStorageWrapsEntityDescriptionCacheFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_entity_description_file_storage_' . bin2hex(random_bytes(4));
        $path = $dir . '/users_cache.php';
        $storage = new entity_description_file_storage();

        mkdir($dir);

        try {
            $this->assertFalse($storage->exists($path));
            $this->assertNotFalse($storage->write($path, '<?php return [];'));
            $this->assertTrue($storage->exists($path));
            $this->assertSame('<?php return [];', file_get_contents($path));
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
