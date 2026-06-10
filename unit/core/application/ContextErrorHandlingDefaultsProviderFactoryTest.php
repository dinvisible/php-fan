<?php

declare(strict_types=1);

use fan\core\bootstrap\bootstrap_error_handler_setup;
use fan\core\di\context_error_handling_defaults_provider_factory;
use fan\core\runtime\error_handler_registrar;
use fan\core\runtime\error_handler_setup;
use fan\core\runtime\error_handling_defaults_factory;
use fan\core\runtime\php_runtime_settings;
use PHPUnit\Framework\TestCase;

final class ContextErrorHandlingDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesContextErrorHandlingDefaultsProvider(): void
    {
        $provider = (new context_error_handling_defaults_provider_factory())();
        $defaults = $provider();

        $this->assertInstanceOf(error_handling_defaults_factory::class, $provider);
        $this->assertInstanceOf(bootstrap_error_handler_setup::class, $defaults['bootstrapErrorHandlerSetup']);
        $this->assertIsCallable($defaults['bootstrapErrorHandlerSetup']);
        $this->assertInstanceOf(php_runtime_settings::class, $defaults['phpRuntimeSettingsFactory']());
        $this->assertInstanceOf(error_handler_registrar::class, $defaults['errorHandlerRegistrar']);
        $this->assertInstanceOf(error_handler_setup::class, $defaults['errorHandlerSetup']);
        $this->assertIsCallable($defaults['errorLogger']);
    }

    public function testSourceOwnsGroupedErrorHandlingContextAssembly(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/context_error_handling_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('static fn(): bootstrap_error_handler_setup => new bootstrap_error_handler_setup()', $source);
        $this->assertStringContainsString('static fn(): php_runtime_settings => new php_runtime_settings()', $source);
        $this->assertStringContainsString('static fn(): error_handler_registrar => new error_handler_registrar()', $source);
        $this->assertStringContainsString('): error_handler_setup => new error_handler_setup(', $source);
        $this->assertStringContainsString('(new error_logger_defaults_provider_factory())()->errorLogger()', $source);
        $this->assertStringNotContainsString('context_error_handling_bootstrap_error_handler_setup_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_error_handling_php_runtime_settings_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_error_handling_error_handler_registrar_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_error_handling_error_handler_setup_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_error_handling_error_logger_defaults_provider_factory', $source);
    }
}
