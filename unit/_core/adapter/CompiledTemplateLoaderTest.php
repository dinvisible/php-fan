<?php

declare(strict_types=1);

use fan\core\adapter\compiled_template_loader;
use fan\core\adapter\compiled_template_loader_state;
use PHPUnit\Framework\TestCase;

final class CompiledTemplateLoaderTest extends TestCase
{
    public function testLoadsCompiledClassThroughInjectedState(): void
    {
        $state = new compiled_template_loader_state();
        $loader = new compiled_template_loader($state);
        $className = 'CompiledTemplateLoaderFixture_' . str_replace('.', '', uniqid('', true));
        $path = sys_get_temp_dir() . '/' . $className . '.php';

        file_put_contents($path, '<?php class ' . $className . ' {}');

        try {
            $this->assertTrue($loader->load($className, $path));
            $this->assertTrue(class_exists($className, false));
            $this->assertSame($path, $state->getPath($className));
            $this->assertTrue($state->isRegistered());
        } finally {
            @unlink($path);
        }
    }

    public function testLoadsThroughInjectedFilesystemAndClassCallbacks(): void
    {
        $state = new compiled_template_loader_state();
        $className = 'CompiledTemplateLoaderInjectedFixture_' . str_replace('.', '', uniqid('', true));
        $path = '/tmp/' . $className . '.php';
        $loaded = false;
        $classExistsCalls = [];
        $readableCalls = [];
        $loadedPaths = [];
        $loader = new compiled_template_loader(
            $state,
            static function (string $className, bool $autoload = true) use (&$classExistsCalls, &$loaded): bool {
                $classExistsCalls[] = [$className, $autoload];
                if ($autoload) {
                    spl_autoload_call($className);
                }

                return $loaded;
            },
            static function (string $path) use (&$readableCalls): bool {
                $readableCalls[] = $path;

                return true;
            },
            static function (string $path) use (&$loadedPaths, &$loaded): void {
                $loadedPaths[] = $path;
                $loaded = true;
            }
        );

        $this->assertTrue($loader->load($className, $path));
        $this->assertSame($path, $state->getPath($className));
        $this->assertTrue($state->isRegistered());
        $this->assertSame([[$className, true]], $classExistsCalls);
        $this->assertSame([$path], $readableCalls);
        $this->assertSame([$path], $loadedPaths);
    }

    public function testUnreadableCompiledTemplateDoesNotInvokeFileLoader(): void
    {
        $state = new compiled_template_loader_state();
        $className = 'CompiledTemplateLoaderUnreadableFixture_' . str_replace('.', '', uniqid('', true));
        $loadedPaths = [];
        $loader = new compiled_template_loader(
            $state,
            static function (string $className, bool $autoload = true): bool {
                if ($autoload) {
                    spl_autoload_call($className);
                }

                return false;
            },
            static fn(string $path): bool => false,
            static function (string $path) use (&$loadedPaths): void {
                $loadedPaths[] = $path;
            }
        );

        $this->assertFalse($loader->load($className, '/tmp/' . $className . '.php'));
        $this->assertSame([], $loadedPaths);
    }

    public function testSourceRequiresInjectedState(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/compiled_template_loader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('compiled_template_loader_state $state,', $source);
        $this->assertStringContainsString('?callable $classExists = null', $source);
        $this->assertStringContainsString('?callable $isReadable = null', $source);
        $this->assertStringContainsString('?callable $fileLoader = null', $source);
        $this->assertStringContainsString('private \Closure $classExists;', $source);
        $this->assertStringContainsString('private \Closure $isReadable;', $source);
        $this->assertStringContainsString('private \Closure $fileLoader;', $source);
        $this->assertStringContainsString('$this->classExists = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->isReadable = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->fileLoader = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('return ($this->classExists)($className, true);', $source);
        $this->assertStringContainsString('$isReadable = $this->isReadable;', $source);
        $this->assertStringContainsString('$fileLoader = $this->fileLoader;', $source);
        $this->assertStringContainsString('$fileLoader($path);', $source);
        $this->assertStringNotContainsString('?compiled_template_loader_state $state = null', $source);
        $this->assertStringNotContainsString('new compiled_template_loader_state()', $source);
        $this->assertStringNotContainsString('return class_exists($className, true);', $source);
        $this->assertStringNotContainsString('&& is_readable($path)', $source);
    }
}
