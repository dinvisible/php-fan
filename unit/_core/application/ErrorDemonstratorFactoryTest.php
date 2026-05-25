<?php

declare(strict_types=1);

use fan\core\runtime\error_demonstrator_factory;
use PHPUnit\Framework\TestCase;

final class ErrorDemonstratorFactoryTest extends TestCase
{
    public function testSourceOwnsDemonstratorBoundaryConstruction(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/runtime/error_demonstrator_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_demonstrator_factory', $source);
        $this->assertStringContainsString('private object $headerWriter', $source);
        $this->assertStringContainsString('private object $errorLogWriter', $source);
        $this->assertStringContainsString('private object $fileStorage', $source);
        $this->assertStringContainsString('private object $demonstratorLoader', $source);
        $this->assertStringContainsString('$demonstrator = $this->demonstratorLoader()->create($errMsg, $tplName, $input);', $source);
        $this->assertStringContainsString('$demonstrator->setPhpArrayFileLoader($phpArrayFileLoader);', $source);
        $this->assertStringContainsString('$demonstrator->setHeaderWriter($this->headerWriter());', $source);
        $this->assertStringContainsString('$demonstrator->setErrorLogWriter($this->errorLogWriter());', $source);
        $this->assertStringContainsString('$demonstrator->setFileStorage($this->fileStorage());', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\header_writer()', $source);
        $this->assertStringNotContainsString('new error_log_writer()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\error_demonstrator_file_storage()', $source);
        $this->assertStringNotContainsString('include ', $source);
        $this->assertStringNotContainsString('require_once ', $source);
        $this->assertStringNotContainsString('is_file(', $source);
    }

    public function testFactoryCreatesDemonstratorThroughInjectedLoaderAndInjectsDependencies(): void
    {
        $loader = new ErrorDemonstratorFactoryLoaderDouble();
        $headerWriter = (object)['name' => 'header-writer'];
        $errorLogWriter = (object)['name' => 'error-log-writer'];
        $fileStorage = (object)['name' => 'file-storage'];
        $input = (object)['name' => 'input'];
        $phpArrayLoader = static fn(): array => [];

        $factory = new error_demonstrator_factory($headerWriter, $errorLogWriter, $fileStorage, $loader);
        $demonstrator = $factory(['message'], 'error_500', $input, $phpArrayLoader);

        $this->assertInstanceOf(ErrorDemonstratorFactoryDemonstratorDouble::class, $demonstrator);
        $this->assertSame([[['message'], 'error_500', $input]], $loader->calls);
        $this->assertSame($phpArrayLoader, $demonstrator->phpArrayFileLoader);
        $this->assertSame($headerWriter, $demonstrator->headerWriter);
        $this->assertSame($errorLogWriter, $demonstrator->errorLogWriter);
        $this->assertSame($fileStorage, $demonstrator->fileStorage);
    }
}

final class ErrorDemonstratorFactoryLoaderDouble
{
    public array $calls = [];

    public function create(array $errMsg, string $tplName, object $input): object
    {
        $this->calls[] = [$errMsg, $tplName, $input];

        return new ErrorDemonstratorFactoryDemonstratorDouble();
    }
}

final class ErrorDemonstratorFactoryDemonstratorDouble
{
    public mixed $phpArrayFileLoader = null;
    public ?object $headerWriter = null;
    public ?object $errorLogWriter = null;
    public ?object $fileStorage = null;

    public function setPhpArrayFileLoader(callable $phpArrayFileLoader): void
    {
        $this->phpArrayFileLoader = $phpArrayFileLoader;
    }

    public function setHeaderWriter(object $headerWriter): void
    {
        $this->headerWriter = $headerWriter;
    }

    public function setErrorLogWriter(object $errorLogWriter): void
    {
        $this->errorLogWriter = $errorLogWriter;
    }

    public function setFileStorage(object $fileStorage): void
    {
        $this->fileStorage = $fileStorage;
    }
}
