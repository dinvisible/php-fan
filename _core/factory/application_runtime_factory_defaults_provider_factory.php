<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\reflection_class_factory;
use fan\core\di\bootstrap_request_input_defaults_factory;


final class application_runtime_factory_defaults_provider_factory
{
    private \Closure $runtimeFactoryProviderFactory;
    private \Closure $configuredConstructionBoundaryFactory;
    private \Closure $requestInputFactoryFactory;
    private \Closure $phpArrayFileLoaderFactory;
    private \Closure $serializerOperationsFactoryFactory;

    public function __construct(
        ?callable $runtimeFactoryProviderFactory = null,
        ?callable $configuredConstructionBoundaryFactory = null,
        ?callable $requestInputFactoryFactory = null,
        ?callable $phpArrayFileLoaderFactory = null,
        ?callable $serializerOperationsFactoryFactory = null
    )
    {
        $this->runtimeFactoryProviderFactory = \Closure::fromCallable(
            $runtimeFactoryProviderFactory
                ?? static fn(callable ...$factories): application_runtime_factory_provider => new application_runtime_factory_provider(
                    ...$factories
                )
        );
        $this->configuredConstructionBoundaryFactory = \Closure::fromCallable(
            $configuredConstructionBoundaryFactory
                ?? static fn(): callable => new configured_service_factory(
                    new configured_class_instantiator(new reflection_class_factory())
                )
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
