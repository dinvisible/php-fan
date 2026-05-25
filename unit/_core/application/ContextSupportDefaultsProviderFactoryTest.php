<?php

declare(strict_types=1);

use fan\core\adapter\bootstrap_loader_file_storage;
use fan\core\adapter\zend_autoloader_loader;
use fan\core\di\context_support_defaults_factory;
use fan\core\di\context_support_defaults_provider_factory;
use PHPUnit\Framework\TestCase;

final class ContextSupportDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesContextSupportDefaultsProvider(): void
    {
        $provider = (new context_support_defaults_provider_factory())();
        $defaults = $provider();

        $this->assertInstanceOf(context_support_defaults_factory::class, $provider);
        foreach ([
            'zendAutoloaderLoaderFactory',
            'bootstrapLoaderFileStorageFactory',
            'bootstrapObjectFactory',
            'bootstrapConfigLoader',
        ] as $key) {
            $this->assertArrayHasKey($key, $defaults);
            $this->assertIsCallable($defaults[$key]);
        }
        $this->assertInstanceOf(zend_autoloader_loader::class, $defaults['zendAutoloaderLoaderFactory']());
        $this->assertInstanceOf(bootstrap_loader_file_storage::class, $defaults['bootstrapLoaderFileStorageFactory']());
    }

    public function testSourceOwnsGroupedSupportContextAssembly(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_support_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('(new bootstrap_autoloader_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('(new bootstrap_loader_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('new bootstrap_config_defaults_factory()', $source);
        $this->assertStringContainsString('(new bootstrap_object_defaults_provider_factory())()', $source);
        $this->assertStringNotContainsString('context_support_autoloader_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_support_loader_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_support_config_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_support_object_defaults_provider_factory', $source);
    }
}
