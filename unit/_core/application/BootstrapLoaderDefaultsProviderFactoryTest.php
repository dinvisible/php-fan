<?php

declare(strict_types=1);

use fan\core\adapter\bootstrap_loader_file_storage;
use fan\core\di\bootstrap_loader_defaults_factory;
use fan\core\di\bootstrap_loader_defaults_provider_factory;
use PHPUnit\Framework\TestCase;

final class BootstrapLoaderDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapLoaderDefaultsProvider(): void
    {
        $provider = (new bootstrap_loader_defaults_provider_factory())();

        $this->assertInstanceOf(bootstrap_loader_defaults_factory::class, $provider);
        $this->assertInstanceOf(bootstrap_loader_file_storage::class, $provider->fileStorage());
    }

    public function testFactoryUsesInjectedFileStorageFactoryProvider(): void
    {
        $fileStorage = (object)['name' => 'bootstrap-loader-file-storage'];
        $providerCalls = 0;
        $provider = (new bootstrap_loader_defaults_provider_factory(
            static function () use (&$providerCalls, $fileStorage): object {
                ++$providerCalls;

                return $fileStorage;
            }
        ))();

        $this->assertSame($fileStorage, $provider->fileStorage());
        $this->assertSame(1, $providerCalls);
    }

    public function testSourceOwnsBootstrapLoaderFileStorageDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_loader_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_loader_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $fileStorageFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(?callable $fileStorageFactoryProvider = null)', $source);
        $this->assertStringContainsString('$this->fileStorageFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(): bootstrap_loader_defaults_factory', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_loader_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../adapter/bootstrap_loader_file_storage.php';", $source);
        $this->assertStringContainsString('return new bootstrap_loader_defaults_factory(', $source);
        $this->assertStringContainsString('$fileStorageFactoryProvider = $this->fileStorageFactoryProvider;', $source);
        $this->assertStringContainsString('$fileStorageFactoryProvider', $source);
        $this->assertStringContainsString('static fn(): object => new bootstrap_loader_file_storage()', $source);
        $this->assertStringNotContainsString('return new bootstrap_loader_defaults_factory(' . "\n" . '            static fn(): object => new bootstrap_loader_file_storage()', $source);
    }
}
