<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_runtime_service_factory_defaults_provider_factory;


final class application_service_engine_factory_defaults_provider_factory
{
    private \Closure $bootstrapRuntimeServiceFactoryFactory;
    private \Closure $serviceEngineFactoryFactory;
    private \Closure $cacheEngineFactoryFactory;
    private \Closure $sessionEngineFactoryFactory;
    private \Closure $userEngineFactoryFactory;

    public function __construct(
        ?callable $bootstrapRuntimeServiceFactoryFactory = null,
        ?callable $serviceEngineFactoryFactory = null,
        ?callable $cacheEngineFactoryFactory = null,
        ?callable $sessionEngineFactoryFactory = null,
        ?callable $userEngineFactoryFactory = null
    )
    {
        $this->bootstrapRuntimeServiceFactoryFactory = \Closure::fromCallable(
            $bootstrapRuntimeServiceFactoryFactory
                ?? static fn(): callable => (new bootstrap_runtime_service_factory_defaults_provider_factory())()
        );
        $this->serviceEngineFactoryFactory = \Closure::fromCallable(
            $serviceEngineFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new service_engine_factory($configuredServiceFactory)
        );
        $this->cacheEngineFactoryFactory = \Closure::fromCallable(
            $cacheEngineFactoryFactory
                ?? static fn(
                    object $serializerOperations,
                    callable $configuredServiceFactory,
                    object $cacheFileStorage
                ): callable => new cache_engine_factory(
                    $serializerOperations,
                    $configuredServiceFactory,
                    $cacheFileStorage,
                    null,
                    null
                )
        );
        $this->sessionEngineFactoryFactory = \Closure::fromCallable(
            $sessionEngineFactoryFactory
                ?? static fn(application_adapter_registry $adapterRegistry): callable => static fn(callable $configuredServiceFactory, object $nativeSession): callable => new session_engine_factory(
                    $configuredServiceFactory,
                    $nativeSession,
                    $adapterRegistry->pearHttpSession()
                )
        );
        $this->userEngineFactoryFactory = \Closure::fromCallable(
            $userEngineFactoryFactory
                ?? static fn(object $serializerOperations, callable $configuredServiceFactory): callable => new user_engine_factory(
                    $serializerOperations,
                    $configuredServiceFactory
                )
        );
    }

    public function __invoke(application_adapter_registry $adapterRegistry): application_service_engine_factory_defaults_provider
    {
        return new application_service_engine_factory_defaults_provider(
            $this->bootstrapRuntimeServiceFactoryFactory,
            $this->serviceEngineFactoryFactory,
            $this->cacheEngineFactoryFactory,
            ($this->sessionEngineFactoryFactory)($adapterRegistry),
            $this->userEngineFactoryFactory
        );
    }
}
