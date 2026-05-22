<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/service/config/base.php';

class ServiceConfigBaseTest extends \PHPUnit\Framework\TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/php-fan-config-' . uniqid('', true);
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
    }

    public function testDirectoryPathIsNormalizedAndExistingFilePathIsResolved(): void
    {
        $loader = new class extends \fan\core\service\config\base {
            protected string $fileExtention = 'conf';
        };
        file_put_contents($this->tmpDir . '/app.conf', 'content');

        $this->assertSame($loader, $loader->setDirPath($this->tmpDir));
        $this->assertSame($this->tmpDir . '/app.conf', $loader->getFilePath('app'));
        $this->assertNull($loader->getFilePath('missing', false));
    }

    public function testBaseLoaderReturnsEmptyArrayForExistingFileWithoutSpecialParser(): void
    {
        $loader = new class extends \fan\core\service\config\base {
        };
        $file = $this->tmpDir . '/plain';
        file_put_contents($file, 'ignored');

        $this->assertSame([], $loader->loadFile($file));
        $this->assertSame([], $loader->loadFile($this->tmpDir . '/missing'));
        $this->assertSame([], $loader->loadFile(null));
    }
}
