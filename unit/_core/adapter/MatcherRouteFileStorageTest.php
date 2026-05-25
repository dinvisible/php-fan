<?php

declare(strict_types=1);

use fan\core\adapter\matcher_route_file_storage;
use PHPUnit\Framework\TestCase;

final class MatcherRouteFileStorageTest extends TestCase
{
    public function testStorageWrapsRouteFileChecks(): void
    {
        $baseDir = sys_get_temp_dir() . '/fan_matcher_route_file_storage_' . uniqid('', true);
        $file = $baseDir . '/index.php';
        $storage = new matcher_route_file_storage();

        try {
            mkdir($baseDir);
            file_put_contents($file, '<?php');

            $this->assertTrue($storage->isDirectory($baseDir));
            $this->assertTrue($storage->isFile($file));
            $this->assertFalse($storage->isDirectory($file));
            $this->assertFalse($storage->isFile($baseDir));
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
