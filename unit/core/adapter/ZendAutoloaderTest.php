<?php

declare(strict_types=1);

use fan\core\adapter\zend_autoloader;
use FanTest\core\SourceFileContractTestCase;

final class AdapterZendAutoloaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/adapter/zend_autoloader.php';

    public function testLoaderUsesInjectedAvailabilityAndFilesystemBoundaries(): void
    {
        $classExistsCalls = [];
        $readableCalls = [];
        $loadedPaths = [];
        $loader = new zend_autoloader(
            static function (string $className, bool $autoload = false) use (&$classExistsCalls): bool {
                $classExistsCalls[] = [$className, $autoload];

                return false;
            },
            static function (string $path) use (&$readableCalls): bool {
                $readableCalls[] = $path;

                return true;
            },
            static function (string $path) use (&$loadedPaths): void {
                $loadedPaths[] = $path;
            }
        );

        $loader->loadPath('/opt/Zend');

        $this->assertSame([['Zend_Loader_Autoloader', false]], $classExistsCalls);
        $this->assertSame(['/opt/Zend/Loader/Autoloader.php'], $readableCalls);
        $this->assertSame(['/opt/Zend/Loader/Autoloader.php'], $loadedPaths);
    }

    public function testLoaderThrowsWhenZendAutoloaderFileIsMissing(): void
    {
        $loadedPaths = [];
        $loader = new zend_autoloader(
            static fn(string $className, bool $autoload = false): bool => false,
            static fn(string $path): bool => false,
            static function (string $path) use (&$loadedPaths): void {
                $loadedPaths[] = $path;
            }
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Zend autoloader is not available at "/missing/Zend/Loader/Autoloader.php".');

        try {
            $loader->loadPath('/missing/Zend');
        } finally {
            $this->assertSame([], $loadedPaths);
        }
    }

    public function testSourceKeepsStaticFacadeAsCompatibilityShell(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private \Closure $classExists;', $source);
        $this->assertStringContainsString('private \Closure $isReadable;', $source);
        $this->assertStringContainsString('private \Closure $fileLoader;', $source);
        $this->assertStringContainsString('public static function load(string $zendPath): void', $source);
        $this->assertStringContainsString('(new self())->loadPath($zendPath);', $source);
        $this->assertStringContainsString('public function loadPath(string $zendPath): void', $source);
        $this->assertStringContainsString('($this->fileLoader)($loaderPath);', $source);
        $this->assertStringNotContainsString("if (class_exists('Zend_Loader_Autoloader', false))", $source);
        $this->assertStringNotContainsString('if (!is_readable($loaderPath))', $source);
    }
}
