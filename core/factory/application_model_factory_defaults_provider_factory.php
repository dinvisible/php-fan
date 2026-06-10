<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\reflection_class_factory;


final class application_model_factory_defaults_provider_factory
{
    private \Closure $modelFactoryProviderFactory;
    private \Closure $entityDesignerFactoryFactory;
    private \Closure $entityEncapsulantFactoryFactory;
    private \Closure $modelEntityFactoryFactory;
    private \Closure $modelRowFactoryFactory;
    private \Closure $modelRowsetFactoryFactory;
    private \Closure $modelRequestFactoryFactory;

    public function __construct(
        ?callable $modelFactoryProviderFactory = null,
        ?callable $entityDesignerFactoryFactory = null,
        ?callable $entityEncapsulantFactoryFactory = null,
        ?callable $modelEntityFactoryFactory = null,
        ?callable $modelRowFactoryFactory = null,
        ?callable $modelRowsetFactoryFactory = null,
        ?callable $modelRequestFactoryFactory = null
    )
    {
        $this->modelFactoryProviderFactory = \Closure::fromCallable(
            $modelFactoryProviderFactory
                ?? static fn(callable ...$factories): application_model_factory_provider => new application_model_factory_provider(
                    ...$factories
                )
        );
        $this->entityDesignerFactoryFactory = \Closure::fromCallable(
            $entityDesignerFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new entity_designer_factory($configuredServiceFactory)
        );
        $this->entityEncapsulantFactoryFactory = \Closure::fromCallable(
            $entityEncapsulantFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new entity_encapsulant_factory($configuredServiceFactory)
        );
        $this->modelEntityFactoryFactory = \Closure::fromCallable(
            $modelEntityFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new model_entity_factory(
                    $configuredServiceFactory,
                    new model_entity_exception_factory($configuredServiceFactory),
                    new reflection_class_factory()
                )
        );
        $this->modelRowFactoryFactory = \Closure::fromCallable(
            $modelRowFactoryFactory
                ?? static fn(object $serializerOperations, callable $configuredServiceFactory): callable => new model_row_factory(
                    $serializerOperations,
                    $configuredServiceFactory,
                    new model_row_exception_factory($configuredServiceFactory)
                )
        );
        $this->modelRowsetFactoryFactory = \Closure::fromCallable(
            $modelRowsetFactoryFactory
                ?? static fn(object $serializerOperations, callable $configuredServiceFactory): callable => new model_rowset_factory(
                    $serializerOperations,
                    $configuredServiceFactory
                )
        );
        $this->modelRequestFactoryFactory = \Closure::fromCallable(
            $modelRequestFactoryFactory
                ?? static fn(object $modelRequestFileStorage): callable => static fn(callable $configuredServiceFactory): callable => new model_request_factory(
                    $configuredServiceFactory,
                    $modelRequestFileStorage
                )
        );
    }

    public function __invoke(application_adapter_registry $adapterRegistry): application_model_factory_defaults_provider
    {
        $modelRequestFileStorage = $adapterRegistry->modelRequestFileStorage();

        return new application_model_factory_defaults_provider(
            $this->modelFactoryProviderFactory,
            $this->entityDesignerFactoryFactory,
            $this->entityEncapsulantFactoryFactory,
            $this->modelEntityFactoryFactory,
            $this->modelRowFactoryFactory,
            $this->modelRowsetFactoryFactory,
            ($this->modelRequestFactoryFactory)($modelRequestFileStorage)
        );
    }
}
