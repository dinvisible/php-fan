<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_factory_provider_defaults_provider
{
    public function __construct(
        private application_model_factory_defaults_provider $modelFactoryDefaultsProvider,
        private application_service_factory_defaults_provider $serviceFactoryDefaultsProvider,
        private application_runtime_factory_defaults_provider $runtimeFactoryDefaultsProvider,
        private application_deferred_service_factory_provider $deferredServiceFactoryProvider
    ) {
    }

    public function applicationModelFactoryProvider(): application_model_factory_provider
    {
        return $this->modelFactoryDefaultsProvider->applicationModelFactoryProvider();
    }

    public function applicationServiceFactoryRegistry(): service_factory_registry
    {
        return $this->serviceFactoryDefaultsProvider->applicationServiceFactoryRegistry();
    }

    public function applicationRuntimeFactoryProvider(): application_runtime_factory_provider
    {
        return $this->runtimeFactoryDefaultsProvider->applicationRuntimeFactoryProvider();
    }

    public function applicationDeferredServiceFactoryProvider(): application_deferred_service_factory_provider
    {
        return $this->deferredServiceFactoryProvider;
    }
}
