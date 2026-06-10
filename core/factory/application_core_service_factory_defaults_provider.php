<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_service_factory_defaults_provider
{
    private \Closure $requestServiceFactoryFactory;
    private \Closure $roleServiceFactoryFactory;
    private \Closure $errorServiceFactoryFactory;
    private \Closure $reflectorServiceFactoryFactory;
    private \Closure $applicationServiceFactoryFactory;
    private \Closure $debugServiceFactoryFactory;
    private \Closure $headerServiceFactoryFactory;
    private \Closure $matcherServiceFactoryFactory;
    private \Closure $timerServiceFactoryFactory;
    private \Closure $plainServiceFactoryFactory;
    private \Closure $localeServiceFactoryFactory;
    private \Closure $timerProgramFactoryFactory;

    public function __construct(
        callable $requestServiceFactoryFactory,
        callable $roleServiceFactoryFactory,
        callable $errorServiceFactoryFactory,
        callable $reflectorServiceFactoryFactory,
        callable $applicationServiceFactoryFactory,
        callable $debugServiceFactoryFactory,
        callable $headerServiceFactoryFactory,
        callable $matcherServiceFactoryFactory,
        callable $timerServiceFactoryFactory,
        callable $plainServiceFactoryFactory,
        callable $localeServiceFactoryFactory,
        callable $timerProgramFactoryFactory
    ) {
        $this->requestServiceFactoryFactory = \Closure::fromCallable($requestServiceFactoryFactory);
        $this->roleServiceFactoryFactory = \Closure::fromCallable($roleServiceFactoryFactory);
        $this->errorServiceFactoryFactory = \Closure::fromCallable($errorServiceFactoryFactory);
        $this->reflectorServiceFactoryFactory = \Closure::fromCallable($reflectorServiceFactoryFactory);
        $this->applicationServiceFactoryFactory = \Closure::fromCallable($applicationServiceFactoryFactory);
        $this->debugServiceFactoryFactory = \Closure::fromCallable($debugServiceFactoryFactory);
        $this->headerServiceFactoryFactory = \Closure::fromCallable($headerServiceFactoryFactory);
        $this->matcherServiceFactoryFactory = \Closure::fromCallable($matcherServiceFactoryFactory);
        $this->timerServiceFactoryFactory = \Closure::fromCallable($timerServiceFactoryFactory);
        $this->plainServiceFactoryFactory = \Closure::fromCallable($plainServiceFactoryFactory);
        $this->localeServiceFactoryFactory = \Closure::fromCallable($localeServiceFactoryFactory);
        $this->timerProgramFactoryFactory = \Closure::fromCallable($timerProgramFactoryFactory);
    }

    public function requestServiceFactory(): callable
    {
        return $this->requestServiceFactoryFactory;
    }

    public function roleServiceFactory(): callable
    {
        return $this->roleServiceFactoryFactory;
    }

    public function errorServiceFactory(): callable
    {
        return $this->errorServiceFactoryFactory;
    }

    public function reflectorServiceFactory(): callable
    {
        return $this->reflectorServiceFactoryFactory;
    }

    public function applicationServiceFactory(): callable
    {
        return $this->applicationServiceFactoryFactory;
    }

    public function debugServiceFactory(): callable
    {
        return $this->debugServiceFactoryFactory;
    }

    public function headerServiceFactory(): callable
    {
        return $this->headerServiceFactoryFactory;
    }

    public function matcherServiceFactory(): callable
    {
        return $this->matcherServiceFactoryFactory;
    }

    public function timerServiceFactory(): callable
    {
        return $this->timerServiceFactoryFactory;
    }

    public function plainServiceFactory(): callable
    {
        return $this->plainServiceFactoryFactory;
    }

    public function localeServiceFactory(): callable
    {
        return $this->localeServiceFactoryFactory;
    }

    public function timerProgramFactory(): callable
    {
        return $this->timerProgramFactoryFactory;
    }
}
