<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\application_container_defaults_factory;
use fan\core\di\application_container_factory_callable_factory;

final class application_container_defaults_provider_factory
{
    private \Closure $bootstrapOperationsFactoryProvider;
    private \Closure $registryDefaultsProviderFactoryProvider;
    private \Closure $factoryProviderDefaultsProviderFactoryProvider;
    private \Closure $registrarDefaultsProviderFactoryProvider;
    private \Closure $creatorDefaultsProviderFactoryProvider;
    private \Closure $containerFactoryCallableFactory;

    public function __construct(
        ?callable $bootstrapOperationsFactoryProvider = null,
        ?callable $registryDefaultsProviderFactoryProvider = null,
        ?callable $factoryProviderDefaultsProviderFactoryProvider = null,
        ?callable $registrarDefaultsProviderFactoryProvider = null,
        ?callable $creatorDefaultsProviderFactoryProvider = null,
        ?callable $containerFactoryCallableFactory = null
    ) {
        $this->bootstrapOperationsFactoryProvider = \Closure::fromCallable(
            $bootstrapOperationsFactoryProvider
                ?? static fn(): callable => (new application_container_operations_defaults_provider_factory())()
        );
        $this->registryDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $registryDefaultsProviderFactoryProvider
                ?? static fn(): callable => (new application_container_registry_defaults_provider_factory())()
        );
        $this->factoryProviderDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $factoryProviderDefaultsProviderFactoryProvider
                ?? static fn(): callable => (new application_container_factory_provider_defaults_provider_factory())()
        );
        $this->registrarDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $registrarDefaultsProviderFactoryProvider
                ?? static fn(): callable => (new application_container_registrar_defaults_provider_factory())()
        );
        $this->creatorDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $creatorDefaultsProviderFactoryProvider
                ?? static fn(): callable => (new application_container_creator_defaults_provider_factory())()
        );
        $this->containerFactoryCallableFactory = \Closure::fromCallable(
            $containerFactoryCallableFactory
                ?? static fn(
                    callable $bootstrapOperationsFactory,
                    callable $registryDefaultsProviderFactory,
                    callable $factoryProviderDefaultsProviderFactory,
                    callable $registrarDefaultsProviderFactory,
                    callable $creatorDefaultsProviderFactory
                ): callable => (new application_container_factory_callable_factory())(
                    $bootstrapOperationsFactory,
                    $registryDefaultsProviderFactory,
                    $factoryProviderDefaultsProviderFactory,
                    $registrarDefaultsProviderFactory,
                    $creatorDefaultsProviderFactory
                )
        );
    }

    public function __invoke(): application_container_defaults_factory
    {
        $bootstrapOperationsFactory = ($this->bootstrapOperationsFactoryProvider)();
        $registryDefaultsProviderFactory = ($this->registryDefaultsProviderFactoryProvider)();
        $factoryProviderDefaultsProviderFactory = ($this->factoryProviderDefaultsProviderFactoryProvider)();
        $registrarDefaultsProviderFactory = ($this->registrarDefaultsProviderFactoryProvider)();
        $creatorDefaultsProviderFactory = ($this->creatorDefaultsProviderFactoryProvider)();
        $containerFactory = ($this->containerFactoryCallableFactory)(
            $bootstrapOperationsFactory,
            $registryDefaultsProviderFactory,
            $factoryProviderDefaultsProviderFactory,
            $registrarDefaultsProviderFactory,
            $creatorDefaultsProviderFactory
        );

        return new application_container_defaults_factory(
            static fn(): callable => $containerFactory
        );
    }
}
