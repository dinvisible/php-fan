<?php

declare(strict_types=1);
use fan\core\service\config\base;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../../_core/service/config/base.php';

class ServiceConfigBaseTest extends TestCase
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
        $loader = new class extends base {
            protected string $fileExtention = 'conf';
        };
        $path = $this->tmpDir . '/app.conf';
        $loader->setFileStorage(new ServiceConfigBaseFileStorageDouble([$path]));

        $this->assertSame($loader, $loader->setDirPath($this->tmpDir));
        $this->assertSame($this->tmpDir . '/app.conf', $loader->getFilePath('app'));
        $this->assertNull($loader->getFilePath('missing', false));
    }

    public function testBaseLoaderReturnsEmptyArrayForExistingFileWithoutSpecialParser(): void
    {
        $loader = new class extends base {
        };
        $file = $this->tmpDir . '/plain';
        $loader->setFileStorage(new ServiceConfigBaseFileStorageDouble([$file]));

        $this->assertSame([], $loader->loadFile($file));
        $this->assertSame([], $loader->loadFile($this->tmpDir . '/missing'));
        $this->assertSame([], $loader->loadFile(null));
    }

    public function testSourceUsesFacadeExceptionFactory(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../_core/service/config/base.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private function createConfigFatalException(', $source);
        $this->assertStringContainsString('$this->facade->createConfigFatalException($message, $code, $previous)', $source);
        $this->assertStringContainsString('$this->createConfigFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }
}

final class ServiceConfigBaseFileStorageDouble
{
    /**
     * @param list<string> $existingPaths
     */
    public function __construct(private array $existingPaths)
    {
    }

    public function exists(string $path): bool
    {
        return in_array($path, $this->existingPaths, true);
    }
}
