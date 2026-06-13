<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_request_input_defaults_factory;


final class application_runtime_factory_defaults_provider_factory
{
    private \Closure $runtimeFactoryProviderFactory;
    private \Closure $configuredConstructionBoundaryFactory;
    private \Closure $configuredServiceFactoryProvider;
    private \Closure $classInstantiatorProvider;
    private \Closure $requestInputFactoryFactory;
    private \Closure $phpArrayFileLoaderFactory;
    private \Closure $serializerOperationsFactoryFactory;

    public function __construct(
        ?callable $runtimeFactoryProviderFactory = null,
        ?callable $configuredConstructionBoundaryFactory = null,
        ?callable $requestInputFactoryFactory = null,
        ?callable $phpArrayFileLoaderFactory = null,
        ?callable $serializerOperationsFactoryFactory = null,
        ?callable $configuredServiceFactoryProvider = null,
        ?callable $classInstantiatorProvider = null
    )
    {
        $this->runtimeFactoryProviderFactory = \Closure::fromCallable(
            $runtimeFactoryProviderFactory
                ?? static fn(callable ...$factories): application_runtime_factory_provider => new application_runtime_factory_provider(
                    ...$factories
                )
        );
        $this->configuredServiceFactoryProvider = \Closure::fromCallable(
            $configuredServiceFactoryProvider
                ?? new application_runtime_configured_service_provider()
        );
        $this->classInstantiatorProvider = \Closure::fromCallable(
            $classInstantiatorProvider
                ?? new application_runtime_class_instantiator_provider()
        );
        $this->configuredConstructionBoundaryFactory = \Closure::fromCallable(
            $configuredConstructionBoundaryFactory
                ?? fn(): callable => ($this->configuredServiceFactoryProvider)(($this->classInstantiatorProvider)())
        );
        $this->requestInputFactoryFactory = \Closure::fromCallable(
            $requestInputFactoryFactory
                ?? static fn(): callable => new bootstrap_request_input_defaults_factory()->requestInputFactory()
        );
        $this->phpArrayFileLoaderFactory = \Closure::fromCallable(
            $phpArrayFileLoaderFactory
                ?? static fn(application_adapter_registry $adapterRegistry): callable => $adapterRegistry->phpArrayFileLoader()
        );
        $this->serializerOperationsFactoryFactory = \Closure::fromCallable(
            $serializerOperationsFactoryFactory
                ?? static fn(application_adapter_registry $adapterRegistry): callable => $adapterRegistry->serializerOperationsFactory()
        );
    }

    public function __invoke(application_adapter_registry $adapterRegistry): application_runtime_factory_defaults_provider
    {
        return new application_runtime_factory_defaults_provider(
            $this->runtimeFactoryProviderFactory,
            ($this->configuredConstructionBoundaryFactory)(),
            ($this->requestInputFactoryFactory)(),
            ($this->phpArrayFileLoaderFactory)($adapterRegistry),
            ($this->serializerOperationsFactoryFactory)($adapterRegistry)
        );
    }
}
