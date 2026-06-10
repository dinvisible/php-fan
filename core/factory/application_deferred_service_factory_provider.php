<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_deferred_service_factory_provider
{
    public function cacheEngineFactory(
        ?callable $override,
        object $serializerOperations,
        callable $configuredServiceFactory,
        application_adapter_registry $adapterRegistry,
        service_factory_registry $serviceFactoryRegistry
    ): callable {
        return $override ?? ($serviceFactoryRegistry->get('cacheEngineFactory'))(
            $serializerOperations,
            $configuredServiceFactory,
            $adapterRegistry->cacheFileStorage()
        );
    }

    public function sessionEngineFactory(
        ?callable $override,
        callable $configuredServiceFactory,
        application_adapter_registry $adapterRegistry,
        service_factory_registry $serviceFactoryRegistry
    ): callable {
        return $override ?? ($serviceFactoryRegistry->get('sessionEngineFactory'))(
            $configuredServiceFactory,
            $adapterRegistry->nativeSession()
        );
    }

    public function userEngineFactory(
        ?callable $override,
        object $serializerOperations,
        callable $configuredServiceFactory,
        service_factory_registry $serviceFactoryRegistry
    ): callable {
        return $override ?? ($serviceFactoryRegistry->get('userEngineFactory'))($serializerOperations, $configuredServiceFactory);
    }

    public function modelRowFactory(
        ?callable $override,
        object $serializerOperations,
        callable $configuredServiceFactory,
        application_model_factory_provider $modelFactoryProvider
    ): callable {
        return $override ?? $modelFactoryProvider->modelRowFactory($serializerOperations, $configuredServiceFactory);
    }

    public function modelRowsetFactory(
        ?callable $override,
        object $serializerOperations,
        callable $configuredServiceFactory,
        application_model_factory_provider $modelFactoryProvider
    ): callable {
        return $override ?? $modelFactoryProvider->modelRowsetFactory($serializerOperations, $configuredServiceFactory);
    }
}
