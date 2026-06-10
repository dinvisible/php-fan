<?php

declare(strict_types=1);

use fan\core\adapter\php_array_file;
use fan\core\adapter\php_array_file_loader;
use PHPUnit\Framework\TestCase;

final class PhpArrayFileLoaderTest extends TestCase
{
    public function testLoaderReturnsDefaultForUnreadableFile(): void
    {
        $loader = new php_array_file_loader();

        $this->assertSame(['fallback' => true], $loader('/tmp/php-fan-missing-file.php', ['fallback' => true]));
    }

    public function testPhpArrayFileUsesInjectedFilesystemCallbacks(): void
    {
        $readableCalls = [];
        $readCalls = [];
        $adapter = new php_array_file(
            static function (string $path) use (&$readableCalls): bool {
                $readableCalls[] = $path;

                return $path === '/tmp/config.php';
            },
            static function (string $path) use (&$readCalls): array {
                $readCalls[] = $path;

                return ['loaded' => $path];
            }
        );

        $this->assertSame(['loaded' => '/tmp/config.php'], $adapter('/tmp/config.php', []));
        $this->assertSame(['fallback' => true], $adapter->loadFile('/tmp/missing.php', ['fallback' => true]));
        $this->assertSame(['/tmp/config.php', '/tmp/missing.php'], $readableCalls);
        $this->assertSame(['/tmp/config.php'], $readCalls);
    }

    public function testLoaderDelegatesToInjectedPhpArrayFileCallable(): void
    {
        $calls = [];
        $loader = new php_array_file_loader(
            static function (string $path, mixed $default = null) use (&$calls): mixed {
                $calls[] = [$path, $default];

                return ['injected' => $path];
            }
        );

        $this->assertSame(['injected' => '/tmp/config.php'], $loader('/tmp/config.php', []));
        $this->assertSame([['/tmp/config.php', []]], $calls);
    }

    public function testPhpArrayFileCanLoadInObjectContext(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'php-fan-array-context-');
        file_put_contents($file, "<?php\nreturn \$this->value();\n");
        $context = new class {
            public function value(): string
            {
                return 'context-value';
            }
        };

        try {
            $loader = new php_array_file_loader();

            $this->assertSame('context-value', $loader($file, null, $context));
        } finally {
            unlink($file);
        }
    }

    public function testSourceOwnsPhpArrayStaticAdapterBoundary(): void
    {
        $adapterSource = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/php_array_file.php');
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/php_array_file_loader.php');

        $this->assertIsString($adapterSource);
        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $isReadable;', $adapterSource);
        $this->assertStringContainsString('private \Closure $fileReader;', $adapterSource);
        $this->assertStringContainsString('public function __construct(?callable $isReadable = null, ?callable $fileReader = null)', $adapterSource);
        $this->assertStringContainsString('public function __invoke(string $path, mixed $default = null, ?object $context = null): mixed', $adapterSource);
        $this->assertStringContainsString('return (new self())->loadFile($path, $default, $context);', $adapterSource);
        $this->assertStringContainsString(')->call($context, $path);', $adapterSource);
        $this->assertStringContainsString('$context === null ? ($this->fileReader)($path) : ($this->fileReader)($path, $context)', $adapterSource);
        $this->assertStringContainsString('final class php_array_file_loader', $source);
        $this->assertStringContainsString('private \Closure $phpArrayFile;', $source);
        $this->assertStringContainsString('public function __construct(?callable $phpArrayFile = null)', $source);
        $this->assertStringContainsString('$this->phpArrayFile = \Closure::fromCallable($phpArrayFile ?? new php_array_file());', $source);
        $this->assertStringContainsString('public function __invoke(string $path, mixed $default = null, ?object $context = null): mixed', $source);
        $this->assertStringContainsString('$context === null ? ($this->phpArrayFile)($path, $default) : ($this->phpArrayFile)($path, $default, $context)', $source);
        $this->assertStringNotContainsString('php_array_file::load($path, $default)', $source);
        $this->assertStringNotContainsString('return includeFile($path)', $adapterSource);
    }
}
