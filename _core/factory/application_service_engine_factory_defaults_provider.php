<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_engine_factory_defaults_provider
{
    private \Closure $bootstrapRuntimeServiceFactoryFactory;
    private \Closure $serviceEngineFactoryFactory;
    private \Closure $cacheEngineFactoryFactory;
    private \Closure $sessionEngineFactoryFactory;
    private \Closure $userEngineFactoryFactory;

    public function __construct(
        callable $bootstrapRuntimeServiceFactoryFactory,
        callable $serviceEngineFactoryFactory,
        callable $cacheEngineFactoryFactory,
        callable $sessionEngineFactoryFactory,
        callable $userEngineFactoryFactory
    ) {
        $this->bootstrapRuntimeServiceFactoryFactory = \Closure::fromCallable($bootstrapRuntimeServiceFactoryFactory);
        $this->serviceEngineFactoryFactory = \Closure::fromCallable($serviceEngineFactoryFactory);
        $this->cacheEngineFactoryFactory = \Closure::fromCallable($cacheEngineFactoryFactory);
        $this->sessionEngineFactoryFactory = \Closure::fromCallable($sessionEngineFactoryFactory);
        $this->userEngineFactoryFactory = \Closure::fromCallable($userEngineFactoryFactory);
    }

    public function bootstrapRuntimeServiceFactory(): callable
    {
        return $this->bootstrapRuntimeServiceFactoryFactory;
    }

    public function serviceEngineFactory(): callable
    {
        return $this->serviceEngineFactoryFactory;
    }

    public function cacheEngineFactory(): callable
    {
        return $this->cacheEngineFactoryFactory;
    }

    public function sessionEngineFactory(): callable
    {
        return $this->sessionEngineFactoryFactory;
    }

    public function userEngineFactory(): callable
    {
        return $this->userEngineFactoryFactory;
    }
}
