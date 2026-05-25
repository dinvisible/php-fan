<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\cookie_writer;
use fan\core\adapter\curl_adapter;
use fan\core\adapter\data_loader;
use fan\core\adapter\error_log_writer;
use fan\core\adapter\header_writer;
use fan\core\adapter\native_session;
use fan\core\adapter\pear_http_session;
use fan\core\adapter\php_array_file_loader;
use fan\core\adapter\safe_serializer_operations;
use fan\core\adapter\warning_capture;


final class application_registry_defaults_provider_factory
{
    private \Closure $warningCaptureFactory;
    private \Closure $phpArrayFileLoaderFactory;
    private \Closure $serializerOperationsFactoryFactory;
    private \Closure $nativeSessionFactory;
    private \Closure $pearHttpSessionFactory;
    private \Closure $dataLoaderFactoryFactory;
    private \Closure $errorLogWriterFactoryFactory;
    private \Closure $headerWriterFactoryFactory;
    private \Closure $cookieWriterFactoryFactory;
    private \Closure $curlAdapterFactoryFactory;
    private \Closure $coreAdapterDefaultsProviderFactory;
    private \Closure $storageAdapterDefaultsProviderFactory;
    private \Closure $runtimeAdapterDefaultsProviderFactory;
    private \Closure $compiledTemplateAdapterDefaultsProviderFactory;
    private \Closure $sessionAdapterDefaultsProviderFactory;
    private \Closure $imageAdapterDefaultsProviderFactory;
    private \Closure $adapterRegistryDefaultsProviderFactory;
    private \Closure $adapterRegistryFactory;
    private \Closure $stateRegistryFactory;

    public function __construct(
        ?callable $warningCaptureFactory = null,
        ?callable $phpArrayFileLoaderFactory = null,
        ?callable $serializerOperationsFactoryFactory = null,
        ?callable $nativeSessionFactory = null,
        ?callable $pearHttpSessionFactory = null,
        ?callable $dataLoaderFactoryFactory = null,
        ?callable $errorLogWriterFactoryFactory = null,
        ?callable $headerWriterFactoryFactory = null,
        ?callable $cookieWriterFactoryFactory = null,
        ?callable $curlAdapterFactoryFactory = null,
        ?callable $coreAdapterDefaultsProviderFactory = null,
        ?callable $storageAdapterDefaultsProviderFactory = null,
        ?callable $runtimeAdapterDefaultsProviderFactory = null,
        ?callable $compiledTemplateAdapterDefaultsProviderFactory = null,
        ?callable $sessionAdapterDefaultsProviderFactory = null,
        ?callable $imageAdapterDefaultsProviderFactory = null,
        ?callable $adapterRegistryDefaultsProviderFactory = null,
        ?callable $adapterRegistryFactory = null,
        ?callable $stateRegistryFactory = null
    )
    {
        $this->warningCaptureFactory = \Closure::fromCallable(
            $warningCaptureFactory
                ?? static fn(): object => new warning_capture()
        );
        $this->phpArrayFileLoaderFactory = \Closure::fromCallable(
            $phpArrayFileLoaderFactory
                ?? static fn(): object => new php_array_file_loader()
        );
        $this->serializerOperationsFactoryFactory = \Closure::fromCallable(
            $serializerOperationsFactoryFactory
                ?? static fn(): callable => static fn(object $warningCapture): object => new safe_serializer_operations(
                    $warningCapture
                )
        );
        $this->nativeSessionFactory = \Closure::fromCallable(
            $nativeSessionFactory
                ?? static fn(): object => new native_session()
        );
        $this->pearHttpSessionFactory = \Closure::fromCallable(
            $pearHttpSessionFactory
                ?? static fn(): object => new pear_http_session()
        );
        $this->dataLoaderFactoryFactory = \Closure::fromCallable(
            $dataLoaderFactoryFactory
                ?? static fn(): callable => static fn(container_interface $container): object => new data_loader(
                    $container->get('request_input'),
                    $container->get('json'),
                    static fn(mixed $value): array => \adduceToArray($value),
                    static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values),
                    $container->get('header_writer')
                )
        );
        $this->errorLogWriterFactoryFactory = \Closure::fromCallable(
            $errorLogWriterFactoryFactory
                ?? static fn(): callable => static function (container_interface $container): object {
                    $runtime = $container->has('bootstrap_runtime') ? $container->get('bootstrap_runtime') : null;
                    $config = $container->has('config') ? ($container->get('config')->get('monolog') ?? []) : [];
                    if ($config === [] && is_object($runtime) && method_exists($runtime, 'getConfigCache')) {
                        try {
                            $configCache = $runtime->getConfigCache();
                            $config = is_array($configCache) ? ($configCache['service']['monolog'] ?? []) : [];
                        } catch (\Throwable) {
                            $config = [];
                        }
                    }

                    return new error_log_writer($config, $runtime);
                }
        );
        $this->headerWriterFactoryFactory = \Closure::fromCallable(
            $headerWriterFactoryFactory
                ?? static fn(): callable => static fn(container_interface $container): object => new header_writer()
        );
        $this->cookieWriterFactoryFactory = \Closure::fromCallable(
            $cookieWriterFactoryFactory
                ?? static fn(): callable => static fn(container_interface $container): object => new cookie_writer()
        );
        $this->curlAdapterFactoryFactory = \Closure::fromCallable(
            $curlAdapterFactoryFactory
                ?? static fn(): callable => static fn(container_interface $container): object => new curl_adapter()
        );
        $this->coreAdapterDefaultsProviderFactory = \Closure::fromCallable(
            $coreAdapterDefaultsProviderFactory
                ?? static fn(
                    object $warningCapture,
                    object $phpArrayFileLoader,
                    callable $serializerOperationsFactory
                ): application_core_adapter_defaults_provider => new application_core_adapter_defaults_provider(
                    $warningCapture,
                    $phpArrayFileLoader,
                    $serializerOperationsFactory
                )
        );
        $this->storageAdapterDefaultsProviderFactory = \Closure::fromCallable(
            $storageAdapterDefaultsProviderFactory
                ?? static fn(): application_storage_adapter_defaults_provider => new application_storage_adapter_defaults_provider()
        );
        $this->runtimeAdapterDefaultsProviderFactory = \Closure::fromCallable(
            $runtimeAdapterDefaultsProviderFactory
                ?? static fn(
                    callable $dataLoaderFactory,
                    callable $errorLogWriterFactory,
                    callable $headerWriterFactory,
                    callable $cookieWriterFactory,
                    callable $curlAdapterFactory
                ): application_runtime_adapter_defaults_provider => new application_runtime_adapter_defaults_provider(
                    $dataLoaderFactory,
                    $errorLogWriterFactory,
                    $headerWriterFactory,
                    $cookieWriterFactory,
                    $curlAdapterFactory
                )
        );
        $this->compiledTemplateAdapterDefaultsProviderFactory = \Closure::fromCallable(
            $compiledTemplateAdapterDefaultsProviderFactory
                ?? static fn(): application_compiled_template_adapter_defaults_provider => new application_compiled_template_adapter_defaults_provider()
        );
        $this->sessionAdapterDefaultsProviderFactory = \Closure::fromCallable(
            $sessionAdapterDefaultsProviderFactory
                ?? static fn(
                    object $nativeSession,
                    object $pearHttpSession
                ): application_session_adapter_defaults_provider => new application_session_adapter_defaults_provider(
                    $nativeSession,
                    $pearHttpSession
                )
        );
        $this->imageAdapterDefaultsProviderFactory = \Closure::fromCallable(
            $imageAdapterDefaultsProviderFactory
                ?? static fn(): application_image_adapter_defaults_provider => new application_image_adapter_defaults_provider()
        );
        $this->adapterRegistryDefaultsProviderFactory = \Closure::fromCallable(
            $adapterRegistryDefaultsProviderFactory
                ?? static fn(
                    application_core_adapter_defaults_provider $coreAdapterDefaultsProvider,
                    application_storage_adapter_defaults_provider $storageAdapterDefaultsProvider,
                    application_runtime_adapter_defaults_provider $runtimeAdapterDefaultsProvider,
                    application_compiled_template_adapter_defaults_provider $compiledTemplateAdapterDefaultsProvider,
                    application_session_adapter_defaults_provider $sessionAdapterDefaultsProvider,
                    application_image_adapter_defaults_provider $imageAdapterDefaultsProvider
                ): application_adapter_registry_defaults_provider => new application_adapter_registry_defaults_provider(
                    $coreAdapterDefaultsProvider,
                    $storageAdapterDefaultsProvider,
                    $runtimeAdapterDefaultsProvider,
                    $compiledTemplateAdapterDefaultsProvider,
                    $sessionAdapterDefaultsProvider,
                    $imageAdapterDefaultsProvider
                )
        );
        $this->adapterRegistryFactory = \Closure::fromCallable(
            $adapterRegistryFactory
                ?? static fn(
                    application_adapter_registry_defaults_provider $defaultsProvider
                ): application_adapter_registry => new application_adapter_registry($defaultsProvider)
        );
        $this->stateRegistryFactory = \Closure::fromCallable(
            $stateRegistryFactory
                ?? static fn(): application_state_registry => new application_state_registry()
        );
    }

    public function __invoke(): application_registry_defaults_provider
    {
        $warningCapture = ($this->warningCaptureFactory)();
        $phpArrayFileLoader = ($this->phpArrayFileLoaderFactory)();
        $serializerOperationsFactory = ($this->serializerOperationsFactoryFactory)();
        $nativeSession = ($this->nativeSessionFactory)();
        $pearHttpSession = ($this->pearHttpSessionFactory)();
        $dataLoaderFactory = ($this->dataLoaderFactoryFactory)();
        $errorLogWriterFactory = ($this->errorLogWriterFactoryFactory)();
        $headerWriterFactory = ($this->headerWriterFactoryFactory)();
        $cookieWriterFactory = ($this->cookieWriterFactoryFactory)();
        $curlAdapterFactory = ($this->curlAdapterFactoryFactory)();
        $coreAdapterDefaultsProvider = ($this->coreAdapterDefaultsProviderFactory)(
            $warningCapture,
            $phpArrayFileLoader,
            $serializerOperationsFactory
        );
        $storageAdapterDefaultsProvider = ($this->storageAdapterDefaultsProviderFactory)();
        $runtimeAdapterDefaultsProvider = ($this->runtimeAdapterDefaultsProviderFactory)(
            $dataLoaderFactory,
            $errorLogWriterFactory,
            $headerWriterFactory,
            $cookieWriterFactory,
            $curlAdapterFactory
        );
        $compiledTemplateAdapterDefaultsProvider = ($this->compiledTemplateAdapterDefaultsProviderFactory)();
        $sessionAdapterDefaultsProvider = ($this->sessionAdapterDefaultsProviderFactory)(
            $nativeSession,
            $pearHttpSession
        );
        $imageAdapterDefaultsProvider = ($this->imageAdapterDefaultsProviderFactory)();
        $adapterRegistryDefaultsProvider = ($this->adapterRegistryDefaultsProviderFactory)(
            $coreAdapterDefaultsProvider,
            $storageAdapterDefaultsProvider,
            $runtimeAdapterDefaultsProvider,
            $compiledTemplateAdapterDefaultsProvider,
            $sessionAdapterDefaultsProvider,
            $imageAdapterDefaultsProvider
        );

        return new application_registry_defaults_provider(
            $adapterRegistryDefaultsProvider,
            $this->adapterRegistryFactory,
            $this->stateRegistryFactory
        );
    }
}
