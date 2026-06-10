<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_factory_provider_defaults_provider_factory
{
    private \Closure $modelFactoryDefaultsProviderFactory;
    private \Closure $serviceFactoryDefaultsProviderFactory;
    private \Closure $runtimeFactoryDefaultsProviderFactory;
    private \Closure $deferredServiceFactoryProviderFactory;

    public function __construct(
        ?callable $modelFactoryDefaultsProviderFactory = null,
        ?callable $serviceFactoryDefaultsProviderFactory = null,
        ?callable $runtimeFactoryDefaultsProviderFactory = null,
        ?callable $deferredServiceFactoryProviderFactory = null
    )
    {
        $this->modelFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $modelFactoryDefaultsProviderFactory
                ?? static fn(application_adapter_registry $adapterRegistry): application_model_factory_defaults_provider => (new application_model_factory_defaults_provider_factory())($adapterRegistry)
        );
        $this->serviceFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $serviceFactoryDefaultsProviderFactory
                ?? static fn(application_adapter_registry $adapterRegistry): application_service_factory_defaults_provider => (new application_service_factory_defaults_provider_factory())($adapterRegistry)
        );
        $this->runtimeFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $runtimeFactoryDefaultsProviderFactory
                ?? static fn(application_adapter_registry $adapterRegistry): application_runtime_factory_defaults_provider => (new application_runtime_factory_defaults_provider_factory())($adapterRegistry)
        );
        $this->deferredServiceFactoryProviderFactory = \Closure::fromCallable(
            $deferredServiceFactoryProviderFactory
                ?? static fn(): application_deferred_service_factory_provider => (new application_deferred_service_factory_provider_factory())()
        );
    }

    public function __invoke(application_adapter_registry $adapterRegistry): application_factory_provider_defaults_provider
    {
        return new application_factory_provider_defaults_provider(
            ($this->modelFactoryDefaultsProviderFactory)($adapterRegistry),
            ($this->serviceFactoryDefaultsProviderFactory)($adapterRegistry),
            ($this->runtimeFactoryDefaultsProviderFactory)($adapterRegistry),
            ($this->deferredServiceFactoryProviderFactory)()
        );
    }
}
