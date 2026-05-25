<?php

declare(strict_types=1);

use fan\core\bootstrap\loader;
use FanTest\_core\SourceFileContractTestCase;

final class BootstrapLoaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/application/loader.php';

    public function testSourceUsesInjectedZendLoaderBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$zendAutoloaderLoader', $source);
        $this->assertStringContainsString('$this->zendAutoloaderLoader()->load($zendPath);', $source);
        $this->assertStringNotContainsString('zend_autoloader::load', $source);
        $this->assertStringNotContainsString('fan\\project\\adapter', $source);
    }

    public function testSourceUsesInjectedAutoloadRegistrationErrorLogger(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$autoloadRegistrationErrorLogger', $source);
        $this->assertStringContainsString('callable $autoloadRegistrationErrorLogger', $source);
        $this->assertStringContainsString('\Closure::fromCallable($autoloadRegistrationErrorLogger)', $source);
        $this->assertStringContainsString('autoloadRegistrationErrorLogger()', $source);
        $this->assertStringNotContainsString('defaultAutoloadRegistrationErrorLogger', $source);
        $this->assertStringNotContainsString('new autoload_registration_error_logger()', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/autoload_registration_error_logger.php';", $source);
        $this->assertStringNotContainsString('error_log($message, 0)', $source);
        $this->assertStringNotContainsString('\bootstrap::logError', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
    }

    public function testLoadFileUsesInjectedFatalExceptionFactoryForFatalMode(): void
    {
        $loader = (new ReflectionClass(loader::class))->newInstanceWithoutConstructor();
        $messages = [];

        foreach ([
            'config' => [],
            'nsKeys' => ['core' => '/core', 'project' => '/project', 'app' => '/app', 'model' => '/model'],
            'extraKeys' => ['capp' => null, 'main' => null, 'temp' => null],
            'fileStorage' => new BootstrapLoaderFileStorageDouble(),
            'fatalExceptionFactory' => Closure::fromCallable(static function (string $message) use (&$messages): Throwable {
                $messages[] = $message;

                return new RuntimeException('fatal: ' . $message);
            }),
        ] as $propertyName => $value) {
            $property = new ReflectionProperty(loader::class, $propertyName);
            $property->setValue($loader, $value);
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('fatal: File "/missing.php" doesn\'t exists');

        try {
            $loader->loadFile('/missing.php', 2);
        } finally {
            $this->assertSame(['File "/missing.php" doesn\'t exists'], $messages);
        }
    }

    public function testLoadFileDelegatesReadableFileToInjectedFileLoader(): void
    {
        $this->defineLoaderDirSeparator();
        $loader = (new ReflectionClass(loader::class))->newInstanceWithoutConstructor();
        $loaded = [];

        foreach ([
            'config' => [],
            'nsKeys' => ['core' => '/core', 'project' => '/project', 'app' => '/app', 'model' => '/model'],
            'extraKeys' => ['capp' => null, 'main' => null, 'temp' => null],
            'fileStorage' => new BootstrapLoaderReadableFileStorageDouble(['/tmp/bootstrap-loader.php']),
            'fileLoader' => Closure::fromCallable(static function (string $path, int $way) use (&$loaded): string {
                $loaded[] = [$path, $way];

                return 'loaded:' . $path . ':' . $way;
            }),
        ] as $propertyName => $value) {
            $property = new ReflectionProperty(loader::class, $propertyName);
            $property->setValue($loader, $value);
        }

        $this->assertSame('loaded:/tmp/bootstrap-loader.php:2', $loader->loadFile('/tmp/bootstrap-loader.php', 0, 2));
        $this->assertSame([['/tmp/bootstrap-loader.php', 2]], $loaded);
    }

    public function testRegisterAutoloadReportsRegistrationFailureThroughInjectedLogger(): void
    {
        $loader = (new ReflectionClass(loader::class))->newInstanceWithoutConstructor();
        $messages = [];

        $property = new ReflectionProperty(loader::class, 'autoloadRegistrationErrorLogger');
        $property->setValue(
            $loader,
            static function (string $message) use (&$messages): void {
                $messages[] = $message;
            }
        );
        $property = new ReflectionProperty(loader::class, 'autoloadRegistrar');
        $property->setValue(
            $loader,
            Closure::fromCallable(static function (): void {
                throw new RuntimeException('MissingClass registration failed.');
            })
        );

        $loader->registerAutoload(['MissingClass', 'missingMethod']);

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Can\'t register autoloader:', $messages[0]);
        $this->assertStringContainsString('MissingClass', $messages[0]);
    }

    public function testAutoloadRegistrationUsesInjectedOperations(): void
    {
        $loader = (new ReflectionClass(loader::class))->newInstanceWithoutConstructor();
        $registered = [];
        $unregistered = [];

        foreach ([
            'autoloadRegistrar' => Closure::fromCallable(static function (mixed $function, bool $prepend) use (&$registered): void {
                $registered[] = [$function, $prepend];
            }),
            'autoloadUnregistrar' => Closure::fromCallable(static function (mixed $function) use (&$unregistered): void {
                $unregistered[] = $function;
            }),
        ] as $propertyName => $value) {
            $property = new ReflectionProperty(loader::class, $propertyName);
            $property->setValue($loader, $value);
        }

        $loader->registerAutoload('spl_autoload_call', true);
        $loader->unregisterAutoload('spl_autoload_call');

        $this->assertSame([['spl_autoload_call', true]], $registered);
        $this->assertSame(['spl_autoload_call'], $unregistered);
    }

    public function testProjectFallbackLoadClassUsesInjectedOperations(): void
    {
        $this->defineLoaderDirSeparator();
        $loader = (new ReflectionClass(loader::class))->newInstanceWithoutConstructor();
        $loaded = [];
        $aliases = [];
        $symbolChecks = [];

        foreach ([
            'cntAliasArg' => 3,
            'nsKeys' => ['core' => '/core', 'project' => '/project', 'app' => '/app', 'model' => '/model'],
            'extraKeys' => ['capp' => null, 'main' => null, 'temp' => null],
            'fileStorage' => new BootstrapLoaderReadableFileStorageDouble(['/core/adapter/example.php']),
            'fileLoader' => Closure::fromCallable(static function (string $path, int $way) use (&$loaded): void {
                $loaded[] = [$path, $way];
            }),
            'classAliaser' => Closure::fromCallable(static function (string $original, string $alias, int $cntAliasArg) use (&$aliases): void {
                $aliases[] = [$original, $alias, $cntAliasArg];
            }),
            'symbolExists' => Closure::fromCallable(static function (string $class) use (&$symbolChecks): bool {
                $symbolChecks[] = $class;

                return $class === 'fan\core\adapter\example';
            }),
        ] as $propertyName => $value) {
            $property = new ReflectionProperty(loader::class, $propertyName);
            $property->setValue($loader, $value);
        }

        $this->assertTrue($loader->loadClass('fan\project\adapter\example'));
        $this->assertSame([['/core/adapter/example.php', 3]], $loaded);
        $this->assertSame([['fan\core\adapter\example', 'fan\project\adapter\example', 3]], $aliases);
        $this->assertSame(['fan\project\adapter\example', 'fan\core\adapter\example'], $symbolChecks);
    }

    private function defineLoaderDirSeparator(): void
    {
        if (!defined('fan\core\bootstrap\DIR_SEPARATOR')) {
            define('fan\core\bootstrap\DIR_SEPARATOR', '/');
        }
    }

    public function testRegisterZend2RequiresInjectedLoader(): void
    {
        $loader = (new ReflectionClass(loader::class))->newInstanceWithoutConstructor();
        $includePath = get_include_path();

        try {
            $loader->registerZend2('/tmp/Zend');
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Zend autoloader loader is not configured.', $exception->getMessage());
        } finally {
            set_include_path($includePath);
        }
    }

    public function testGetRealPathRequiresInjectedFileStorage(): void
    {
        $loader = (new ReflectionClass(loader::class))->newInstanceWithoutConstructor();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap loader file storage is not configured.');

        $loader->getRealPath('/tmp/missing-loader-path.php');
    }
}

final class BootstrapLoaderFileStorageDouble
{
    public function isFile(string $path): bool
    {
        return false;
    }

    public function isDirectory(string $path): bool
    {
        return false;
    }

    public function realPath(string $path): string|false
    {
        return false;
    }

    public function isReadable(string $path): bool
    {
        return false;
    }
}

final class BootstrapLoaderReadableFileStorageDouble
{
    /**
     * @param list<string> $readablePaths
     */
    public function __construct(private array $readablePaths)
    {
    }

    public function isFile(string $path): bool
    {
        return in_array($path, $this->readablePaths, true);
    }

    public function isDirectory(string $path): bool
    {
        return false;
    }

    public function realPath(string $path): string|false
    {
        return in_array($path, $this->readablePaths, true) ? $path : false;
    }

    public function isReadable(string $path): bool
    {
        return in_array($path, $this->readablePaths, true);
    }
}
