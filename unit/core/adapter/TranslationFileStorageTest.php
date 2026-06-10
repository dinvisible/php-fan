<?php

declare(strict_types=1);

use fan\core\adapter\translation_file_storage;
use PHPUnit\Framework\TestCase;

final class TranslationFileStorageTest extends TestCase
{
    public function testStorageWrapsTranslationFileOperations(): void
    {
        $file = sys_get_temp_dir() . '/fan_translation_file_storage_' . bin2hex(random_bytes(4)) . '.php';
        $storage = new translation_file_storage();

        try {
            $this->assertFalse($storage->isReadable($file));
            $data = '<?php return [];';
            $this->assertSame(strlen($data), $storage->write($file, $data));
            $this->assertTrue($storage->isReadable($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
