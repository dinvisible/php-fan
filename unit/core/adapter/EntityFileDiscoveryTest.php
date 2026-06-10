<?php

declare(strict_types=1);

use fan\core\adapter\entity_file_discovery;
use PHPUnit\Framework\TestCase;

final class EntityFileDiscoveryTest extends TestCase
{
    public function testDiscoveryWrapsEntityDirectoryFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_entity_file_discovery_' . bin2hex(random_bytes(4));
        $entityDir = $dir . '/user';
        $file = $entityDir . '/entity.php';
        $discovery = new entity_file_discovery();

        mkdir($dir);
        mkdir($entityDir);
        file_put_contents($file, '<?php');

        try {
            $this->assertContains('user', $discovery->scanDirectory($dir));
            $this->assertTrue($discovery->isDirectory($entityDir));
            $this->assertTrue($discovery->exists($file));
            $this->assertFalse($discovery->exists($entityDir . '/missing.php'));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
            if (is_dir($entityDir)) {
                rmdir($entityDir);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
