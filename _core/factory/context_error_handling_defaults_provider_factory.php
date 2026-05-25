<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\bootstrap_error_handler_setup;
use fan\core\runtime\error_handler_registrar;
use fan\core\runtime\error_handler_setup;
use fan\core\runtime\error_handling_defaults_factory;
use fan\core\runtime\php_runtime_settings;

final class context_error_handling_defaults_provider_factory
{
    public function __invoke(): error_handling_defaults_factory
    {
        $bootstrapErrorHandlerSetupFactory = static fn(): bootstrap_error_handler_setup => new bootstrap_error_handler_setup();
        $phpRuntimeSettingsFactory = static fn(): php_runtime_settings => new php_runtime_settings();
        $errorHandlerRegistrarFactory = static fn(): error_handler_registrar => new error_handler_registrar();
        $errorHandlerSetupFactory = static fn(
            object $phpRuntimeSettings,
            callable $errorHandlerRegistrar
        ): error_handler_setup => new error_handler_setup(
            $phpRuntimeSettings,
            $errorHandlerRegistrar
        );
        $errorLoggerFactory = static fn(): callable => (new error_logger_defaults_provider_factory())()->errorLogger();

        return new error_handling_defaults_factory(
            $bootstrapErrorHandlerSetupFactory,
            $phpRuntimeSettingsFactory,
            $errorHandlerRegistrarFactory,
            $errorHandlerSetupFactory,
            $errorLoggerFactory
        );
    }
}
