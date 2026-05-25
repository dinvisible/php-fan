<?php

declare(strict_types=1);

use fan\core\adapter\restore_password_log_storage;
use PHPUnit\Framework\TestCase;

final class RestorePasswordLogStorageTest extends TestCase
{
    public function testStorageListsLogFilesNewestNameFirstAndReadsLines(): void
    {
        $dir = sys_get_temp_dir() . '/fan_restore_password_log_storage_' . bin2hex(random_bytes(4));
        mkdir($dir);
        $storage = new restore_password_log_storage();

        try {
            file_put_contents($dir . '/b.log', "b1\nb2\n");
            file_put_contents($dir . '/a.log', "a1\n");
            file_put_contents($dir . '/ignore.txt', 'skip');

            $this->assertSame(['b.log', 'a.log'], $storage->logFiles($dir));
            $this->assertSame(["b1\n", "b2\n"], $storage->readLines($dir . '/b.log'));
        } finally {
            foreach (['b.log', 'a.log', 'ignore.txt'] as $file) {
                $path = $dir . '/' . $file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
