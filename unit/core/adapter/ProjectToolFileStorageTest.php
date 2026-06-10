<?php

declare(strict_types=1);

use fan\core\adapter\project_tool_file_storage;
use PHPUnit\Framework\TestCase;

final class ProjectToolFileStorageTest extends TestCase
{
    public function testStorageWrapsProjectToolFilesystemOperations(): void
    {
        $baseDir = sys_get_temp_dir() . '/fan_project_tool_file_storage_' . uniqid('', true);
        $sourceDir = $baseDir . '/source';
        $targetDir = $baseDir . '/target';
        $sourceFile = $sourceDir . '/entity_user.php';
        $copyFile = $targetDir . '/entity_user.php';
        $storage = new project_tool_file_storage();

        try {
            $this->assertTrue($storage->makeDirectory($sourceDir, 0777, true));
            $this->assertTrue($storage->isDirectory($sourceDir));
            $this->assertSame(7, $storage->write($sourceFile, 'content'));
            $this->assertTrue($storage->isFile($sourceFile));
            $this->assertTrue($storage->isWritable($sourceFile));
            $this->assertSame('content', $storage->read($sourceFile));
            $this->assertContains('entity_user.php', $storage->scanDirectory($sourceDir));
            $this->assertSame(realpath($sourceFile), $storage->realPath($sourceFile));

            $this->assertTrue($storage->makeDirectory($targetDir));
            $this->assertTrue($storage->copy($sourceFile, $copyFile));
            $this->assertSame('content', $storage->read($copyFile));
        } finally {
            if (is_file($copyFile)) {
                unlink($copyFile);
            }
            if (is_file($sourceFile)) {
                unlink($sourceFile);
            }
            if (is_dir($targetDir)) {
                rmdir($targetDir);
            }
            if (is_dir($sourceDir)) {
                rmdir($sourceDir);
            }
            if (is_dir($baseDir)) {
                rmdir($baseDir);
            }
        }
    }
}
