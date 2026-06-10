<?php

declare(strict_types=1);

use fan\core\adapter\bootstrap_loader_file_storage;
use PHPUnit\Framework\TestCase;

final class BootstrapLoaderFileStorageTest extends TestCase
{
    public function testStorageWrapsBootstrapLoaderFilesystemOperations(): void
    {
        $dir = sys_get_temp_dir() . '/fan_bootstrap_loader_file_storage_' . bin2hex(random_bytes(4));
        mkdir($dir);
        $file = $dir . '/load.php';
        file_put_contents($file, '<?php return true;');
        $storage = new bootstrap_loader_file_storage();

        try {
            $this->assertTrue($storage->exists($file));
            $this->assertTrue($storage->isDirectory($dir));
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
            $this->assertSame(realpath($file), $storage->realPath($file));
            $this->assertTrue($storage->load($file, 2));
            $this->assertContains('load.php', $storage->scanDirectory($dir));
            $this->assertTrue($storage->symbolExists(bootstrap_loader_file_storage::class));
            $this->assertFalse($storage->exists($file . '.missing'));
            $this->assertFalse($storage->isFile($file . '.missing'));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function testStorageWrapsBootstrapLoaderRuntimeOperations(): void
    {
        $storage = new bootstrap_loader_file_storage();
        $loader = static function (): void {
        };
        $alias = 'BootstrapLoaderFileStorageTestAlias_' . str_replace('.', '_', uniqid('', true));

        try {
            $storage->registerAutoload($loader);
            $this->assertContains($loader, spl_autoload_functions() ?: []);

            $storage->aliasClass(BootstrapLoaderFileStorageAliasSource::class, $alias, 3);
            $this->assertTrue($storage->symbolExists($alias));
        } finally {
            $storage->unregisterAutoload($loader);
        }
    }
}

final class BootstrapLoaderFileStorageAliasSource
{
}
