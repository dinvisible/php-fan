<?php

declare(strict_types=1);

use fan\core\di\application_adapter_registry;
use fan\core\di\application_adapter_registry_defaults_provider;
use fan\core\di\application_compiled_template_adapter_defaults_provider;
use fan\core\di\application_core_adapter_defaults_provider;
use fan\core\di\application_image_adapter_defaults_provider;
use fan\core\di\application_registry_defaults_provider;
use fan\core\di\application_runtime_adapter_defaults_provider;
use fan\core\di\application_session_adapter_defaults_provider;
use fan\core\di\application_state_registry;
use fan\core\di\application_storage_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\cookie_writer;
use fan\core\adapter\curl_adapter;
use fan\core\adapter\data_loader;
use fan\core\adapter\error_log_writer;
use fan\core\adapter\header_writer;
use fan\core\adapter\php_array_file_loader;
use fan\core\adapter\safe_serializer_operations;
use fan\core\adapter\warning_capture;
use fan\core\di\container_interface;


final class ApplicationRegistryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesApplicationRegistryDefaults(): void
    {
        $defaultsProvider = self::defaultsProvider();

        $this->assertInstanceOf(application_adapter_registry::class, $defaultsProvider->applicationAdapterRegistry());
        $this->assertInstanceOf(application_state_registry::class, $defaultsProvider->applicationStateRegistry());
    }

    private static function defaultsProvider(): application_registry_defaults_provider
    {
        return new application_registry_defaults_provider(
            new application_adapter_registry_defaults_provider(
                self::coreAdapterDefaultsProvider(),
                new application_storage_adapter_defaults_provider(),
                self::runtimeAdapterDefaultsProvider(),
                new application_compiled_template_adapter_defaults_provider(),
                new application_session_adapter_defaults_provider(
                    new ApplicationRegistryDefaultsProviderNativeSessionDouble(),
                    new ApplicationRegistryDefaultsProviderPearHttpSessionDouble()
                ),
                new application_image_adapter_defaults_provider()
            ),
            static fn(
                application_adapter_registry_defaults_provider $defaultsProvider
            ): application_adapter_registry => new application_adapter_registry($defaultsProvider),
            static fn(): application_state_registry => new application_state_registry()
        );
    }

    private static function coreAdapterDefaultsProvider(): application_core_adapter_defaults_provider
    {
        return new application_core_adapter_defaults_provider(
            new warning_capture(),
            new php_array_file_loader(),
            static fn(object $warningCapture): object => new safe_serializer_operations($warningCapture)
        );
    }

    private static function runtimeAdapterDefaultsProvider(): application_runtime_adapter_defaults_provider
    {
        return new application_runtime_adapter_defaults_provider(
            static fn(container_interface $container): object => new data_loader(
                $container->get('request_input'),
                $container->get('json'),
                static fn(mixed $value): array => \adduceToArray($value),
                static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values),
                $container->get('header_writer')
            ),
            static fn(container_interface $container): object => new error_log_writer(),
            static fn(container_interface $container): object => new header_writer(),
            static fn(container_interface $container): object => new cookie_writer(),
            static fn(container_interface $container): object => new curl_adapter()
        );
    }
}

final class ApplicationRegistryDefaultsProviderNativeSessionDouble
{
}

final class ApplicationRegistryDefaultsProviderPearHttpSessionDouble
{
}
