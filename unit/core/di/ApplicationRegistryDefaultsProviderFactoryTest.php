<?php

declare(strict_types=1);

use fan\core\di\application_adapter_registry;
use fan\core\di\application_adapter_registry_defaults_provider;
use fan\core\di\application_compiled_template_adapter_defaults_provider;
use fan\core\di\application_core_adapter_defaults_provider;
use fan\core\di\application_image_adapter_defaults_provider;
use fan\core\di\application_registry_defaults_provider;
use fan\core\di\application_registry_defaults_provider_factory;
use fan\core\di\application_runtime_adapter_defaults_provider;
use fan\core\di\application_session_adapter_defaults_provider;
use fan\core\di\application_state_registry;
use fan\core\di\application_storage_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;

final class ApplicationRegistryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationRegistryDefaultsProvider(): void
    {
        $provider = (new application_registry_defaults_provider_factory())();

        $this->assertInstanceOf(application_registry_defaults_provider::class, $provider);
        $this->assertInstanceOf(application_adapter_registry::class, $provider->applicationAdapterRegistry());
        $this->assertInstanceOf(application_state_registry::class, $provider->applicationStateRegistry());
    }

    public function testFactoryAcceptsInjectedRegistryDefaults(): void
    {
        $warningCapture = (object)['name' => 'warning'];
        $phpArrayFileLoader = new ApplicationRegistryDefaultsProviderFactoryCallableDouble();
        $serializerOperationsFactory = static fn(object $warningCapture): object => (object)['warningCapture' => $warningCapture];
        $nativeSession = (object)['name' => 'native-session'];
        $pearHttpSession = (object)['name' => 'pear-http-session'];
        $dataLoaderFactory = static fn(): object => (object)['name' => 'data-loader'];
        $errorLogWriterFactory = static fn(): object => (object)['name' => 'error-log-writer'];
        $headerWriterFactory = static fn(): object => (object)['name' => 'header-writer'];
        $cookieWriterFactory = static fn(): object => (object)['name' => 'cookie-writer'];
        $curlAdapterFactory = static fn(): object => (object)['name' => 'curl-adapter'];
        $coreAdapterDefaultsProvider = new application_core_adapter_defaults_provider(
            $warningCapture,
            $phpArrayFileLoader,
            $serializerOperationsFactory
        );
        $storageAdapterDefaultsProvider = new application_storage_adapter_defaults_provider();
        $runtimeAdapterDefaultsProvider = new application_runtime_adapter_defaults_provider(
            $dataLoaderFactory,
            $errorLogWriterFactory,
            $headerWriterFactory,
            $cookieWriterFactory,
            $curlAdapterFactory
        );
        $compiledTemplateAdapterDefaultsProvider = new application_compiled_template_adapter_defaults_provider();
        $sessionAdapterDefaultsProvider = new application_session_adapter_defaults_provider(
            $nativeSession,
            $pearHttpSession
        );
        $imageAdapterDefaultsProvider = new application_image_adapter_defaults_provider();
        $adapterRegistryDefaultsProvider = null;
        $adapterRegistry = null;
        $stateRegistry = new application_state_registry();

        $provider = (new application_registry_defaults_provider_factory(
            static fn(): object => $warningCapture,
            static fn(): object => $phpArrayFileLoader,
            static fn(): callable => $serializerOperationsFactory,
            static fn(): object => $nativeSession,
            static fn(): object => $pearHttpSession,
            static fn(): callable => $dataLoaderFactory,
            static fn(): callable => $errorLogWriterFactory,
            static fn(): callable => $headerWriterFactory,
            static fn(): callable => $cookieWriterFactory,
            static fn(): callable => $curlAdapterFactory,
            function (
                object $warningCaptureArgument,
                object $phpArrayFileLoaderArgument,
                callable $serializerOperationsFactoryArgument
            ) use (
                $warningCapture,
                $phpArrayFileLoader,
                $serializerOperationsFactory,
                $coreAdapterDefaultsProvider
            ): application_core_adapter_defaults_provider {
                $this->assertSame($warningCapture, $warningCaptureArgument);
                $this->assertSame($phpArrayFileLoader, $phpArrayFileLoaderArgument);
                $this->assertSame($serializerOperationsFactory, $serializerOperationsFactoryArgument);

                return $coreAdapterDefaultsProvider;
            },
            static fn(): application_storage_adapter_defaults_provider => $storageAdapterDefaultsProvider,
            function (
                callable $dataLoaderFactoryArgument,
                callable $errorLogWriterFactoryArgument,
                callable $headerWriterFactoryArgument,
                callable $cookieWriterFactoryArgument,
                callable $curlAdapterFactoryArgument
            ) use (
                $dataLoaderFactory,
                $errorLogWriterFactory,
                $headerWriterFactory,
                $cookieWriterFactory,
                $curlAdapterFactory,
                $runtimeAdapterDefaultsProvider
            ): application_runtime_adapter_defaults_provider {
                $this->assertSame($dataLoaderFactory, $dataLoaderFactoryArgument);
                $this->assertSame($errorLogWriterFactory, $errorLogWriterFactoryArgument);
                $this->assertSame($headerWriterFactory, $headerWriterFactoryArgument);
                $this->assertSame($cookieWriterFactory, $cookieWriterFactoryArgument);
                $this->assertSame($curlAdapterFactory, $curlAdapterFactoryArgument);

                return $runtimeAdapterDefaultsProvider;
            },
            static fn(): application_compiled_template_adapter_defaults_provider => $compiledTemplateAdapterDefaultsProvider,
            function (
                object $nativeSessionArgument,
                object $pearHttpSessionArgument
            ) use (
                $nativeSession,
                $pearHttpSession,
                $sessionAdapterDefaultsProvider
            ): application_session_adapter_defaults_provider {
                $this->assertSame($nativeSession, $nativeSessionArgument);
                $this->assertSame($pearHttpSession, $pearHttpSessionArgument);

                return $sessionAdapterDefaultsProvider;
            },
            static fn(): application_image_adapter_defaults_provider => $imageAdapterDefaultsProvider,
            function (
                application_core_adapter_defaults_provider $coreProviderArgument,
                application_storage_adapter_defaults_provider $storageProviderArgument,
                application_runtime_adapter_defaults_provider $runtimeProviderArgument,
                application_compiled_template_adapter_defaults_provider $compiledTemplateProviderArgument,
                application_session_adapter_defaults_provider $sessionProviderArgument,
                application_image_adapter_defaults_provider $imageProviderArgument
            ) use (
                &$adapterRegistryDefaultsProvider,
                $coreAdapterDefaultsProvider,
                $storageAdapterDefaultsProvider,
                $runtimeAdapterDefaultsProvider,
                $compiledTemplateAdapterDefaultsProvider,
                $sessionAdapterDefaultsProvider,
                $imageAdapterDefaultsProvider
            ): application_adapter_registry_defaults_provider {
                $this->assertSame($coreAdapterDefaultsProvider, $coreProviderArgument);
                $this->assertSame($storageAdapterDefaultsProvider, $storageProviderArgument);
                $this->assertSame($runtimeAdapterDefaultsProvider, $runtimeProviderArgument);
                $this->assertSame($compiledTemplateAdapterDefaultsProvider, $compiledTemplateProviderArgument);
                $this->assertSame($sessionAdapterDefaultsProvider, $sessionProviderArgument);
                $this->assertSame($imageAdapterDefaultsProvider, $imageProviderArgument);

                return $adapterRegistryDefaultsProvider = new application_adapter_registry_defaults_provider(
                    $coreProviderArgument,
                    $storageProviderArgument,
                    $runtimeProviderArgument,
                    $compiledTemplateProviderArgument,
                    $sessionProviderArgument,
                    $imageProviderArgument
                );
            },
            function (
                application_adapter_registry_defaults_provider $defaultsProvider
            ) use (&$adapterRegistry, &$adapterRegistryDefaultsProvider): application_adapter_registry {
                $this->assertSame($adapterRegistryDefaultsProvider, $defaultsProvider);

                return $adapterRegistry = new application_adapter_registry($defaultsProvider);
            },
            static fn(): application_state_registry => $stateRegistry
        ))();

        $this->assertInstanceOf(application_registry_defaults_provider::class, $provider);
        $createdAdapterRegistry = $provider->applicationAdapterRegistry();

        $this->assertSame($adapterRegistry, $createdAdapterRegistry);
        $this->assertSame($stateRegistry, $provider->applicationStateRegistry());
    }
}

final class ApplicationRegistryDefaultsProviderFactoryCallableDouble
{
    public function __invoke(string $path): array
    {
        return [$path];
    }
}
