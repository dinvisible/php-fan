<?php

declare(strict_types=1);

use fan\core\adapter\project_tool_loader;
use PHPUnit\Framework\TestCase;

final class ProjectToolLoaderTest extends TestCase
{
    public function testLoaderUsesInjectedFilesystemAndClassDependencies(): void
    {
        $classExistsCalls = [];
        $readableCalls = [];
        $loadedPaths = [];
        $loaded = false;
        $loader = new project_tool_loader(
            static function (string $className, bool $autoload = true) use (&$classExistsCalls, &$loaded): bool {
                $classExistsCalls[] = [$className, $autoload];

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

        $this->assertTrue($loader('project_tool_class', '/tmp/project-tool.php'));
        $this->assertSame([
            ['project_tool_class', true],
            ['project_tool_class', false],
        ], $classExistsCalls);
        $this->assertSame(['/tmp/project-tool.php'], $readableCalls);
        $this->assertSame(['/tmp/project-tool.php'], $loadedPaths);
    }

    public function testLoaderDoesNotLoadUnreadableFile(): void
    {
        $loadedPaths = [];
        $loader = new project_tool_loader(
            static fn(string $className, bool $autoload = true): bool => false,
            static fn(string $path): bool => false,
            static function (string $path) use (&$loadedPaths): void {
                $loadedPaths[] = $path;
            }
        );

        $this->assertFalse($loader->loadTool('missing_project_tool_class', '/tmp/missing-project-tool.php'));
        $this->assertSame([], $loadedPaths);
    }

    public function testSourceKeepsStaticFacadeAsCompatibilityShell(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/project_tool_loader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $classExists;', $source);
        $this->assertStringContainsString('private \Closure $isReadable;', $source);
        $this->assertStringContainsString('private \Closure $fileLoader;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('public function __invoke(string $className, string $path): bool', $source);
        $this->assertStringContainsString('return (new self())->loadTool($className, $path);', $source);
        $this->assertStringContainsString('($this->fileLoader)($path);', $source);
        $this->assertStringContainsString('require_once $path;', $source);
        $this->assertStringNotContainsString('if (class_exists($className)) {', $source);
        $this->assertStringNotContainsString('if (!is_readable($path)) {', $source);
    }
}
