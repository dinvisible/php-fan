<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_object_defaults_factory;
use fan\core\bootstrap\bootstrap_object_factory;


final class bootstrap_object_defaults_provider_factory
{
    private \Closure $objectFactoryFactory;
    private \Closure $configuredServiceFactoryFactory;
    private \Closure $classInstantiatorFactory;
    private \Closure $objectFactoryProvider;
    private \Closure $configuredServiceFactoryProvider;
    private \Closure $classInstantiatorProvider;

    public function __construct(
        ?callable $objectFactoryFactory = null,
        ?callable $configuredServiceFactoryFactory = null,
        ?callable $classInstantiatorFactory = null,
        ?callable $objectFactoryProvider = null,
        ?callable $configuredServiceFactoryProvider = null,
        ?callable $classInstantiatorProvider = null
    ) {
        $this->objectFactoryProvider = \Closure::fromCallable(
            $objectFactoryProvider
                ?? static fn(callable $configuredServiceFactory): callable => new bootstrap_object_factory($configuredServiceFactory)
        );
        $this->configuredServiceFactoryProvider = \Closure::fromCallable(
            $configuredServiceFactoryProvider
                ?? new bootstrap_object_configured_service_provider()
        );
        $this->classInstantiatorProvider = \Closure::fromCallable(
            $classInstantiatorProvider
                ?? new bootstrap_object_class_instantiator_provider()
        );
        $this->objectFactoryFactory = \Closure::fromCallable(
            $objectFactoryFactory
                ?? fn(callable $configuredServiceFactory): callable => ($this->objectFactoryProvider)($configuredServiceFactory)
        );
        $this->configuredServiceFactoryFactory = \Closure::fromCallable(
            $configuredServiceFactoryFactory
                ?? fn(callable $classInstantiator): callable => ($this->configuredServiceFactoryProvider)($classInstantiator)
        );
        $this->classInstantiatorFactory = \Closure::fromCallable(
            $classInstantiatorFactory
                ?? fn(): callable => ($this->classInstantiatorProvider)()
        );
    }

    public function __invoke(): bootstrap_object_defaults_factory
    {
        $classInstantiatorFactory = $this->classInstantiatorFactory;
        $configuredServiceFactoryFactory = $this->configuredServiceFactoryFactory;
        $objectFactoryFactory = $this->objectFactoryFactory;
        $classInstantiator = $classInstantiatorFactory();
        $configuredServiceFactory = $configuredServiceFactoryFactory($classInstantiator);
        $objectFactory = $objectFactoryFactory($configuredServiceFactory);

        return new bootstrap_object_defaults_factory(
            static fn(): callable => $objectFactory
        );
    }
}
