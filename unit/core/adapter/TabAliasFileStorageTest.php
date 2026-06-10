<?php

declare(strict_types=1);

use fan\core\adapter\tab_alias_file_storage;
use PHPUnit\Framework\TestCase;

final class TabAliasFileStorageTest extends TestCase
{
    public function testStorageWrapsAliasFileReadability(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_tab_alias_');
        $this->assertIsString($file);
        $storage = new tab_alias_file_storage();

        try {
            $this->assertTrue($storage->isReadable($file));
        } finally {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->assertFalse($storage->isReadable($file));
    }
}
