<?php

declare(strict_types=1);

use fan\core\runtime\error_handling_defaults_factory;
use fan\core\bootstrap\bootstrap_error_handler_setup;
use fan\core\runtime\error_handler_registrar;
use fan\core\runtime\error_handler_setup;
use fan\core\di\error_logger_defaults_provider_factory;
use fan\core\runtime\php_runtime_settings;
use PHPUnit\Framework\TestCase;

final class BootstrapErrorHandlingDefaultsFactoryTest extends TestCase
{
    public function testFactoryReturnsErrorHandlingDefaults(): void
    {
        $defaults = $this->defaultsFactory()();

        foreach ([
            'bootstrapErrorHandlerSetup',
            'phpRuntimeSettingsFactory',
            'errorHandlerRegistrar',
            'errorHandlerSetup',
            'errorLogger',
        ] as $key) {
            $this->assertArrayHasKey($key, $defaults);
            $this->assertIsCallable($defaults[$key]);
        }

        $this->assertInstanceOf(php_runtime_settings::class, $defaults['phpRuntimeSettingsFactory']());
    }

    public function testSourceOwnsErrorHandlingDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/runtime/error_handling_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_handling_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $bootstrapErrorHandlerSetupFactory;', $source);
        $this->assertStringContainsString('private \Closure $phpRuntimeSettingsFactory;', $source);
        $this->assertStringContainsString('private \Closure $errorHandlerRegistrarFactory;', $source);
        $this->assertStringContainsString('private \Closure $errorHandlerSetupFactory;', $source);
        $this->assertStringContainsString('private \Closure $errorLoggerFactory;', $source);
        $this->assertStringContainsString('callable $bootstrapErrorHandlerSetupFactory', $source);
        $this->assertStringContainsString('callable $errorLoggerFactory', $source);
        $this->assertStringContainsString('$this->bootstrapErrorHandlerSetupFactory = \Closure::fromCallable($bootstrapErrorHandlerSetupFactory);', $source);
        $this->assertStringContainsString('$this->errorLoggerFactory = \Closure::fromCallable($errorLoggerFactory);', $source);
        $this->assertStringContainsString('$phpRuntimeSettings = ($this->phpRuntimeSettingsFactory)();', $source);
        $this->assertStringContainsString('$errorHandlerRegistrar = ($this->errorHandlerRegistrarFactory)();', $source);
        $this->assertStringContainsString("'bootstrapErrorHandlerSetup' => (\$this->bootstrapErrorHandlerSetupFactory)()", $source);
        $this->assertStringContainsString("'phpRuntimeSettingsFactory' => static fn(): object => \$phpRuntimeSettings", $source);
        $this->assertStringContainsString("'errorHandlerRegistrar' => \$errorHandlerRegistrar", $source);
        $this->assertStringContainsString("'errorHandlerSetup' => (\$this->errorHandlerSetupFactory)(\$phpRuntimeSettings, \$errorHandlerRegistrar)", $source);
        $this->assertStringContainsString("'errorLogger' => (\$this->errorLoggerFactory)()", $source);
        $this->assertStringNotContainsString('$phpRuntimeSettings = new php_runtime_settings();', $source);
        $this->assertStringNotContainsString('$errorHandlerRegistrar = new error_handler_registrar();', $source);
        $this->assertStringNotContainsString("'bootstrapErrorHandlerSetup' => new bootstrap_error_handler_setup()", $source);
        $this->assertStringNotContainsString('new error_handler_setup($phpRuntimeSettings, $errorHandlerRegistrar)', $source);
        $this->assertStringNotContainsString("'errorLogger' => error_logger_defaults_factory::errorLogger()", $source);
        $this->assertStringNotContainsString('new error_logger(', $source);
        $this->assertStringNotContainsString('new error_log_writer()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\error_logger_file_storage()', $source);
        $this->assertStringNotContainsString('private function loadDefaultFactories(): void', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/error_logger.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/adapter/error_log_writer.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/adapter/error_logger_file_storage.php';", $source);
    }

    private function defaultsFactory(): error_handling_defaults_factory
    {
        return new error_handling_defaults_factory(
            static fn(): bootstrap_error_handler_setup => new bootstrap_error_handler_setup(),
            static fn(): php_runtime_settings => new php_runtime_settings(),
            static fn(): error_handler_registrar => new error_handler_registrar(),
            static fn(object $phpRuntimeSettings, callable $errorHandlerRegistrar): error_handler_setup => new error_handler_setup(
                $phpRuntimeSettings,
                $errorHandlerRegistrar
            ),
            static fn(): callable => (new error_logger_defaults_provider_factory())()->errorLogger()
        );
    }
}
