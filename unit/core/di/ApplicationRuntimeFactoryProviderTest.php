<?php

declare(strict_types=1);

use fan\core\di\application_runtime_factory_provider;
use PHPUnit\Framework\TestCase;

final class ApplicationRuntimeFactoryProviderTest extends TestCase
{
    public function testProviderUsesInjectedConfiguredConstructionBoundary(): void
    {
        $provider = self::provider(
            configuredConstructionBoundary: static fn(string $className, array $arguments): object => (object)[
                'className' => $className,
                'arguments' => $arguments,
            ]
        );
        $factory = $provider->configuredConstructionBoundary();

        $service = $factory('ExampleService', ['alpha']);

        $this->assertSame('ExampleService', $service->className);
        $this->assertSame(['alpha'], $service->arguments);
    }

    public function testProviderUsesInjectedRequestInputFactory(): void
    {
        $requestInput = new stdClass();
        $provider = self::provider(requestInputFactory: static fn(): object => $requestInput);
        $factory = $provider->requestInputFactory();

        $this->assertSame($requestInput, $factory());
    }

    public function testProviderUsesInjectedPhpArrayLoaderBoundary(): void
    {
        $provider = self::provider(phpArrayFileLoader: static fn(string $path, mixed $default = null): array => ['loaded' => $path]);

        $loader = $provider->phpArrayFileLoader();

        $this->assertSame(['loaded' => 'config.php'], $loader('config.php', []));
    }

    public function testProviderUsesInjectedSerializerOperationsBoundary(): void
    {
        $serializerOperations = new stdClass();
        $receivedWarningCapture = null;
        $provider = self::provider(
            serializerOperationsFactory: static function (object $warningCapture) use ($serializerOperations, &$receivedWarningCapture): object {
                $receivedWarningCapture = $warningCapture;

                return $serializerOperations;
            }
        );
        $warningCapture = new class {
            public function capture(callable $callback): mixed
            {
                return $callback();
            }
        };
        $factory = $provider->serializerOperationsFactory();

        $this->assertSame($serializerOperations, $factory($warningCapture));
        $this->assertSame($warningCapture, $receivedWarningCapture);
    }

    public function testSourceKeepsRuntimeDefaultsOutOfApplicationContainerFactory(): void
    {
        $providerSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_runtime_factory_provider.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_factory_provider_defaults_provider.php');
        $factoryProviderDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $defaultsProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_runtime_factory_defaults_provider.php');
        $defaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_runtime_factory_defaults_provider_factory.php');
        $adapterRegistrySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_adapter_registry.php');
        $adapterDefaultsProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_adapter_registry_defaults_provider.php');
        $coreAdapterDefaultsProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_adapter_defaults_provider.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_container_factory.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_factory_bundle.php');

        $this->assertIsString($providerSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($factoryProviderDefaultsSource);
        $this->assertIsString($factoryProviderDefaultsProviderFactorySource);
        $this->assertIsString($defaultsProviderSource);
        $this->assertIsString($defaultsProviderFactorySource);
        $this->assertIsString($adapterRegistrySource);
        $this->assertIsString($adapterDefaultsProviderSource);
        $this->assertIsString($coreAdapterDefaultsProviderSource);
        $this->assertIsString($containerSource);
        $this->assertIsString($bundleSource);
        $this->assertStringContainsString('callable $configuredConstructionBoundary,', $providerSource);
        $this->assertStringContainsString('callable $requestInputFactory,', $providerSource);
        $this->assertStringContainsString('callable $phpArrayFileLoader,', $providerSource);
        $this->assertStringContainsString('callable $serializerOperationsFactory', $providerSource);
        $this->assertStringContainsString('$this->configuredConstructionBoundary = \Closure::fromCallable($configuredConstructionBoundary);', $providerSource);
        $this->assertStringContainsString('$this->requestInputFactory = \Closure::fromCallable($requestInputFactory);', $providerSource);
        $this->assertStringContainsString('$this->phpArrayFileLoader = \Closure::fromCallable($phpArrayFileLoader);', $providerSource);
        $this->assertStringContainsString('$this->serializerOperationsFactory = \Closure::fromCallable($serializerOperationsFactory);', $providerSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/configured_service_factory.php';", $providerSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/configured_class_instantiator.php';", $providerSource);
        $this->assertStringNotContainsString('new \fan\core\di\configured_service_factory(new \fan\core\di\configured_class_instantiator())', $providerSource);
        $this->assertStringNotContainsString('new \fan\core\runtime\request_input_factory()', $providerSource);
        $this->assertStringNotContainsString("application_runtime_factory_defaults_provider_factory::class,\n            'application_runtime_factory_defaults_provider_factory.php'", $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringContainsString('(new application_runtime_factory_defaults_provider_factory())($adapterRegistry)', $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString("application_runtime_factory_defaults_provider::class,\n            'application_runtime_factory_defaults_provider.php'", $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_runtime_factory_defaults_provider(', $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString("application_runtime_factory_defaults_provider::class,\n            'application_runtime_factory_defaults_provider.php'", $defaultsProviderFactorySource);
        $this->assertStringContainsString('new application_runtime_factory_defaults_provider(', $defaultsProviderFactorySource);
        $this->assertStringContainsString('return $this->factoryProviderDefaultsProvider()->applicationRuntimeFactoryProvider();', $dependencyProviderSource);
        $this->assertStringNotContainsString('new application_runtime_factory_defaults_provider(', $dependencyProviderSource);
        $this->assertStringNotContainsString("self::loadClass(application_runtime_factory_defaults_provider::class, 'application_runtime_factory_defaults_provider.php');", $factoryProviderDefaultsSource);
        $this->assertStringNotContainsString('new application_runtime_factory_defaults_provider(', $factoryProviderDefaultsSource);
        $this->assertStringContainsString('return $this->runtimeFactoryDefaultsProvider->applicationRuntimeFactoryProvider();', $factoryProviderDefaultsSource);
        $this->assertStringContainsString('private \Closure $runtimeFactoryProviderFactory;', $defaultsProviderSource);
        $this->assertStringContainsString('private \Closure $configuredConstructionBoundary;', $defaultsProviderSource);
        $this->assertStringContainsString('private \Closure $requestInputFactory;', $defaultsProviderSource);
        $this->assertStringContainsString('private \Closure $phpArrayFileLoader;', $defaultsProviderSource);
        $this->assertStringContainsString('private \Closure $serializerOperationsFactory;', $defaultsProviderSource);
        $this->assertStringContainsString('$factory = $this->runtimeFactoryProviderFactory;', $defaultsProviderSource);
        $this->assertStringNotContainsString('self::loadClass(', $defaultsProviderSource);
        $this->assertStringNotContainsString('new configured_service_factory(new configured_class_instantiator())', $defaultsProviderSource);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/bootstrap/bootstrap_request_input_defaults_factory.php';", $defaultsProviderSource);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/adapter/request_input_native_environment.php';", $dependencyProviderSource);
        $this->assertStringContainsString('new application_runtime_configured_service_provider()', $defaultsProviderFactorySource);
        $this->assertStringContainsString('new application_runtime_class_instantiator_provider()', $defaultsProviderFactorySource);
        $this->assertStringNotContainsString('new configured_service_factory(', $defaultsProviderFactorySource);
        $this->assertStringNotContainsString('new configured_class_instantiator(', $defaultsProviderFactorySource);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/bootstrap/' . \$fileName;", $defaultsProviderFactorySource);
        $this->assertStringContainsString('new bootstrap_request_input_defaults_factory()->requestInputFactory()', $defaultsProviderFactorySource);
        $this->assertStringNotContainsString('new configured_service_factory(new configured_class_instantiator())', $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/bootstrap/' . \$fileName;", $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new bootstrap_request_input_defaults_factory()->requestInputFactory()', $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/bootstrap/bootstrap_request_input_defaults_factory.php';", $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('\fan\core\bootstrap\bootstrap_request_input_defaults_factory::requestInputFactory()', $factoryProviderDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('return \fan\core\bootstrap\bootstrap_request_input_defaults_factory::requestInputFactory();', $defaultsProviderSource);
        $this->assertStringNotContainsString('new \fan\core\runtime\request_input_defaults_factory(', $defaultsProviderSource);
        $this->assertStringNotContainsString('static fn(): object => new \fan\core\adapter\request_input_native_environment()', $defaultsProviderSource);
        $this->assertStringNotContainsString('private function requestInputSourceFactory(): callable', $dependencyProviderSource);
        $this->assertStringNotContainsString('new \fan\core\runtime\request_input_source_factory(', $defaultsProviderSource);
        $this->assertStringNotContainsString('new \fan\core\adapter\request_input_globals_factory(', $defaultsProviderSource);
        $this->assertStringContainsString('return static fn(): array => [];', $providerSource);
        $this->assertStringNotContainsString('new \fan\core\bootstrap\bootstrap_operations_factory(', $providerSource);
        $this->assertStringNotContainsString('new \fan\core\bootstrap\bootstrap_static_operations()', $providerSource);
        $this->assertStringNotContainsString('new php_array_file_loader()', $providerSource);
        $this->assertStringNotContainsString('new safe_serializer_operations($warningCapture)', $providerSource);
        $this->assertStringContainsString('$this->phpArrayFileLoader = $defaultsProvider->phpArrayFileLoader();', $adapterRegistrySource);
        $this->assertStringContainsString('coreAdapterDefaultsProvider()->phpArrayFileLoader()', $adapterDefaultsProviderSource);
        $this->assertStringContainsString('coreAdapterDefaultsProvider()->serializerOperationsFactory()', $adapterDefaultsProviderSource);
        $this->assertStringNotContainsString('new php_array_file_loader();', $coreAdapterDefaultsProviderSource);
        $this->assertStringNotContainsString('new safe_serializer_operations(', $coreAdapterDefaultsProviderSource);
        $this->assertStringNotContainsString('new php_array_file_loader();', $adapterDefaultsProviderSource);
        $this->assertStringNotContainsString('new safe_serializer_operations(', $adapterDefaultsProviderSource);
        $this->assertStringContainsString('$runtimeFactoryProvider->configuredConstructionBoundary()', $bundleSource);
        $this->assertStringNotContainsString('private static function defaultRequestInputFactory', $containerSource);
        $this->assertStringNotContainsString('private static function defaultBootstrapOperationsFactory', $containerSource);
        $this->assertStringNotContainsString('private static function defaultPhpArrayFileLoader', $containerSource);
        $this->assertStringNotContainsString('private static function defaultSerializerOperationsFactory', $containerSource);
        $this->assertStringNotContainsString('private static function defaultConfiguredConstructionBoundary', $containerSource);
    }

    private static function provider(
        ?callable $configuredConstructionBoundary = null,
        ?callable $requestInputFactory = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $serializerOperationsFactory = null
    ): application_runtime_factory_provider {
        return new application_runtime_factory_provider(
            $configuredConstructionBoundary ?? static fn(string $className, array $arguments): object => new stdClass(),
            $requestInputFactory ?? static fn(): object => new stdClass(),
            $phpArrayFileLoader ?? static fn(string $path, mixed $default = null): mixed => $default,
            $serializerOperationsFactory ?? static fn(object $warningCapture): object => new stdClass()
        );
    }
}
