<?php

declare(strict_types=1);

use fan\core\di\context_support_defaults_factory;
use PHPUnit\Framework\TestCase;

final class ContextSupportDefaultsFactoryTest extends TestCase
{
    public function testFactoryReturnsContextSupportDefaults(): void
    {
        $loader = new stdClass();
        $defaults = (new context_support_defaults_factory(
            static fn(): array => [
                'zendAutoloaderLoaderFactory' => static fn(): object => $loader,
                'bootstrapLoaderFileStorageFactory' => static fn(): object => $loader,
                'bootstrapObjectFactory' => static fn(): object => $loader,
                'bootstrapConfigLoader' => static fn(): array => [],
            ]
        ))();

        foreach ([
            'zendAutoloaderLoaderFactory',
            'bootstrapLoaderFileStorageFactory',
            'bootstrapObjectFactory',
            'bootstrapConfigLoader',
        ] as $key) {
            $this->assertArrayHasKey($key, $defaults);
            $this->assertIsCallable($defaults[$key]);
        }

        $this->assertSame($loader, $defaults['zendAutoloaderLoaderFactory']());
        $this->assertSame($loader, $defaults['bootstrapLoaderFileStorageFactory']());
    }

    public function testSourceUsesInjectedContextSupportDefaultsFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_support_defaults_factory.php');
        $contextDefaultsSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($contextDefaultsSource);
        $this->assertStringContainsString('final class context_support_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $contextSupportDefaultsFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $contextSupportDefaultsFactory)', $source);
        $this->assertStringContainsString('$this->contextSupportDefaultsFactory = \Closure::fromCallable($contextSupportDefaultsFactory);', $source);
        $this->assertStringContainsString('return ($this->contextSupportDefaultsFactory)();', $source);
        $this->assertStringNotContainsString('loadDefaultFactories', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('$bootstrapAutoloaderDefaults = (new bootstrap_autoloader_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('$bootstrapLoaderDefaults = (new bootstrap_loader_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('$bootstrapLoaderFileStorage = $bootstrapLoaderDefaults->fileStorage();', $source);
        $this->assertStringNotContainsString('$bootstrapConfigDefaults = new bootstrap_config_defaults_factory();', $source);
        $this->assertStringNotContainsString('$bootstrapObjectDefaults = (new bootstrap_object_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('$bootstrapObjectFactory = $bootstrapObjectDefaults->objectFactory();', $source);
        $this->assertStringNotContainsString('$bootstrapLoaderFileStorage = new \fan\core\adapter\bootstrap_loader_file_storage();', $source);
        $this->assertStringNotContainsString('bootstrap_loader_defaults_factory::fileStorage()', $source);
        $this->assertStringNotContainsString('$bootstrapObjectFactory = bootstrap_object_defaults_factory::objectFactory();', $source);
        $this->assertStringNotContainsString('new \fan\core\di\configured_service_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\configured_class_instantiator()', $source);
        $this->assertStringNotContainsString("'zendAutoloaderLoaderFactory' => static fn(): object => \$bootstrapAutoloaderDefaults->zendAutoloaderLoader()", $source);
        $this->assertStringNotContainsString('bootstrap_autoloader_defaults_factory::zendAutoloaderLoader()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\zend_autoloader_loader()', $source);
        $this->assertStringNotContainsString("'bootstrapLoaderFileStorageFactory' => static fn(): object => \$bootstrapLoaderFileStorage", $source);
        $this->assertStringNotContainsString("'bootstrapObjectFactory' => \$bootstrapObjectFactory", $source);
        $this->assertStringNotContainsString("'bootstrapObjectFactory' => new bootstrap_object_factory(\$configuredServiceFactory)", $source);
        $this->assertStringNotContainsString("'bootstrapConfigLoader' => \$bootstrapConfigDefaults->configLoader()", $source);
        $this->assertStringNotContainsString('bootstrap_config_defaults_factory::configLoader()', $source);
        $this->assertStringNotContainsString("'bootstrapConfigLoader' => new bootstrap_config_loader()", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_autoloader_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_autoloader_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/adapter/zend_autoloader_loader.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_loader_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_loader_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/adapter/bootstrap_loader_file_storage.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_object_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_object_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_object_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_config_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_config_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_config_loader.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/factory/configured_service_factory.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/di/configured_class_instantiator.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_root_support_defaults_provider_factory.php';", $contextDefaultsSource);
        $this->assertStringContainsString('$contextSupportDefaults = (new context_support_defaults_provider_factory())();', $contextDefaultsSource);
        $this->assertStringContainsString('$supportDefaults = $contextSupportDefaults();', $contextDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_support_defaults_provider_factory.php';", $contextDefaultsSource);
        $this->assertStringNotContainsString('$supportDefaultsProvider = (new context_support_defaults_provider_factory())();', $contextDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_support_defaults_factory.php';", $contextDefaultsSource);
        $this->assertStringNotContainsString('$supportDefaults = (new context_support_defaults_factory())();', $contextDefaultsSource);
        $this->assertStringContainsString('return array_replace($coreDefaults, $supportDefaults, $errorHandlingDefaults);', $contextDefaultsSource);
        $this->assertStringNotContainsString('new \fan\core\adapter\zend_autoloader_loader()', $contextDefaultsSource);
        $this->assertStringNotContainsString('new \fan\core\adapter\bootstrap_loader_file_storage()', $contextDefaultsSource);
        $this->assertStringNotContainsString('new \fan\core\di\configured_service_factory(', $contextDefaultsSource);
        $this->assertStringNotContainsString('new bootstrap_object_factory($configuredServiceFactory)', $contextDefaultsSource);
        $this->assertStringNotContainsString("'bootstrapConfigLoader' => new bootstrap_config_loader()", $contextDefaultsSource);
    }
}
