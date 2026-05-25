<?php

declare(strict_types=1);

use fan\core\bootstrap\bootstrap_operations_factory;
use PHPUnit\Framework\TestCase;

final class BootstrapOperationsFactoryTest extends TestCase
{
    public function testFactoryReturnsBootstrapOperationCallbacks(): void
    {
        $factory = new bootstrap_operations_factory(new BootstrapOperationsFactoryTestOperations());
        $operations = $factory();

        foreach ([
            'getLoader',
            'getRunner',
            'getInitializer',
            'parsePath',
            'loadClass',
            'logError',
            'handleError',
            'getGlobalPath',
            'getConfigCache',
            'getPid',
            'isCli',
        ] as $operationName) {
            $this->assertArrayHasKey($operationName, $operations);
            $this->assertIsCallable($operations[$operationName]);
        }
    }

    public function testFactoryUsesInjectedOperationsObject(): void
    {
        $operations = (new bootstrap_operations_factory(new BootstrapOperationsFactoryTestOperations()))();

        $this->assertSame('config/cache', $operations['getConfigCache']());
        $this->assertSame('parsed:/tmp/file.php', $operations['parsePath']('/tmp/file.php'));
        $this->assertTrue($operations['isCli']());
    }

    public function testFactoryRejectsOperationsObjectWithoutRequiredMethod(): void
    {
        $factory = new bootstrap_operations_factory(new stdClass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap operations object must provide getLoader().');

        $factory();
    }

    public function testSourceUsesInjectableOperationsBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_operations_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_operations_factory', $source);
        $this->assertStringContainsString('public function __construct(object $operations)', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_static_operations.php';", $source);
        $this->assertStringNotContainsString('new bootstrap_static_operations()', $source);
        $this->assertStringNotContainsString('defaultOperations', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
        $this->assertFileDoesNotExist(dirname(__DIR__, 3) . '/_core/application/bootstrap_static_operations.php');
    }
}

final class BootstrapOperationsFactoryTestOperations
{
    public function getLoader(): object
    {
        return new stdClass();
    }

    public function getRunner(): object
    {
        return new stdClass();
    }

    public function getInitializer(): object
    {
        return new stdClass();
    }

    public function parsePath(string $path): string
    {
        return 'parsed:' . $path;
    }

    public function loadClass(string $class, bool $makeAlias = true): mixed
    {
        return [$class, $makeAlias];
    }

    public function logError(string $message): mixed
    {
        return $message;
    }

    public function handleError(
        int|float $errNo,
        string $errMsg,
        ?string $fileName = null,
        int|float|null $lineNum = null,
        mixed $errContext = null
    ): ?bool {
        return true;
    }

    public function getGlobalPath(string $key, mixed $altPath = null): ?string
    {
        return $altPath === null ? $key : (string)$altPath;
    }

    public function getConfigCache(): array|string
    {
        return 'config/cache';
    }

    public function getPid(): string
    {
        return 'pid';
    }

    public function isCli(): bool
    {
        return true;
    }
}
