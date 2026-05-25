<?php

declare(strict_types=1);

use fan\core\adapter\zend_autoloader_loader;
use fan\core\di\bootstrap_autoloader_defaults_factory;
use fan\core\di\bootstrap_autoloader_defaults_provider_factory;
use PHPUnit\Framework\TestCase;

final class BootstrapAutoloaderDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapAutoloaderDefaultsProvider(): void
    {
        $provider = (new bootstrap_autoloader_defaults_provider_factory())();

        $this->assertInstanceOf(bootstrap_autoloader_defaults_factory::class, $provider);
        $this->assertInstanceOf(zend_autoloader_loader::class, $provider->zendAutoloaderLoader());
    }

    public function testFactoryUsesInjectedZendAutoloaderLoaderFactoryProvider(): void
    {
        $loader = (object)['name' => 'zend-autoloader-loader'];
        $providerCalls = 0;
        $provider = (new bootstrap_autoloader_defaults_provider_factory(
            static function () use (&$providerCalls, $loader): object {
                ++$providerCalls;

                return $loader;
            }
        ))();

        $this->assertSame($loader, $provider->zendAutoloaderLoader());
        $this->assertSame(1, $providerCalls);
    }

    public function testSourceOwnsZendAutoloaderLoaderDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_autoloader_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_autoloader_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $zendAutoloaderLoaderFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(?callable $zendAutoloaderLoaderFactoryProvider = null)', $source);
        $this->assertStringContainsString('$this->zendAutoloaderLoaderFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(): bootstrap_autoloader_defaults_factory', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_autoloader_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../adapter/zend_autoloader_loader.php';", $source);
        $this->assertStringContainsString('return new bootstrap_autoloader_defaults_factory(', $source);
        $this->assertStringContainsString('$zendAutoloaderLoaderFactoryProvider = $this->zendAutoloaderLoaderFactoryProvider;', $source);
        $this->assertStringContainsString('$zendAutoloaderLoaderFactoryProvider', $source);
        $this->assertStringContainsString('static fn(): object => new zend_autoloader_loader()', $source);
        $this->assertStringNotContainsString('return new bootstrap_autoloader_defaults_factory(' . "\n" . '            static fn(): object => new zend_autoloader_loader()', $source);
    }
}
