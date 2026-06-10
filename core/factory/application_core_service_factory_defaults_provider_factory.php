<?php

declare(strict_types=1);

namespace fan\core\di;
final class application_core_service_factory_defaults_provider_factory
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
        ?callable $requestServiceFactoryFactory = null,
        ?callable $roleServiceFactoryFactory = null,
        ?callable $errorServiceFactoryFactory = null,
        ?callable $reflectorServiceFactoryFactory = null,
        ?callable $applicationServiceFactoryFactory = null,
        ?callable $debugServiceFactoryFactory = null,
        ?callable $headerServiceFactoryFactory = null,
        ?callable $matcherServiceFactoryFactory = null,
        ?callable $timerServiceFactoryFactory = null,
        ?callable $plainServiceFactoryFactory = null,
        ?callable $localeServiceFactoryFactory = null,
        ?callable $timerProgramFactoryFactory = null
    )
    {
        $this->requestServiceFactoryFactory = \Closure::fromCallable(
            $requestServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new request_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->roleServiceFactoryFactory = \Closure::fromCallable(
            $roleServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new role_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->errorServiceFactoryFactory = \Closure::fromCallable(
            $errorServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new error_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->reflectorServiceFactoryFactory = \Closure::fromCallable(
            $reflectorServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new reflector_service_factory($configuredServiceFactory)
        );
        $this->applicationServiceFactoryFactory = \Closure::fromCallable(
            $applicationServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new application_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->debugServiceFactoryFactory = \Closure::fromCallable(
            $debugServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new debug_service_factory($configuredServiceFactory)
        );
        $this->headerServiceFactoryFactory = \Closure::fromCallable(
            $headerServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new header_service_factory(
                    $configuredServiceFactory,
                    null
                )
        );
        $this->matcherServiceFactoryFactory = \Closure::fromCallable(
            $matcherServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new matcher_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->timerServiceFactoryFactory = \Closure::fromCallable(
            $timerServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new timer_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->plainServiceFactoryFactory = \Closure::fromCallable(
            $plainServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new plain_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->localeServiceFactoryFactory = \Closure::fromCallable(
            $localeServiceFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new locale_service_factory(
                    $configuredServiceFactory
                )
        );
        $this->timerProgramFactoryFactory = \Closure::fromCallable(
            $timerProgramFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new timer_program_factory($configuredServiceFactory)
        );
    }

    public function __invoke(): application_core_service_factory_defaults_provider
    {
        return new application_core_service_factory_defaults_provider(
            $this->requestServiceFactoryFactory,
            $this->roleServiceFactoryFactory,
            $this->errorServiceFactoryFactory,
            $this->reflectorServiceFactoryFactory,
            $this->applicationServiceFactoryFactory,
            $this->debugServiceFactoryFactory,
            $this->headerServiceFactoryFactory,
            $this->matcherServiceFactoryFactory,
            $this->timerServiceFactoryFactory,
            $this->plainServiceFactoryFactory,
            $this->localeServiceFactoryFactory,
            $this->timerProgramFactoryFactory
        );
    }
}
