<?php

declare(strict_types=1);

use fan\core\di\bootstrap_loader_defaults_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\bootstrap_loader_file_storage;


final class BootstrapLoaderDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapLoaderFileStorage(): void
    {
        $factory = new bootstrap_loader_defaults_factory(
            static fn(): object => new bootstrap_loader_file_storage()
        );

        $this->assertInstanceOf(
            bootstrap_loader_file_storage::class,
            $factory->fileStorage()
        );
    }

    public function testSourceOwnsBootstrapLoaderFileStorageDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_loader_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_loader_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $fileStorageFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $fileStorageFactory)', $source);
        $this->assertStringContainsString('$this->fileStorageFactory = \Closure::fromCallable($fileStorageFactory);', $source);
        $this->assertStringContainsString('public function fileStorage(): object', $source);
        $this->assertStringContainsString('return ($this->fileStorageFactory)();', $source);
        $this->assertStringNotContainsString('public static function fileStorage(): object', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\bootstrap_loader_file_storage()', $source);
    }
}
