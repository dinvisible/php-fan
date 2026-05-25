<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_model_factory_provider
{
    private \Closure $entityDesignerFactory;

    private \Closure $entityEncapsulantFactory;

    private \Closure $modelEntityFactory;

    private \Closure $modelRowFactory;

    private \Closure $modelRowsetFactory;

    private \Closure $modelRequestFactory;

    public function __construct(
        callable $entityDesignerFactory,
        callable $entityEncapsulantFactory,
        callable $modelEntityFactory,
        callable $modelRowFactory,
        callable $modelRowsetFactory,
        callable $modelRequestFactory
    ) {
        $this->entityDesignerFactory = \Closure::fromCallable($entityDesignerFactory);
        $this->entityEncapsulantFactory = \Closure::fromCallable($entityEncapsulantFactory);
        $this->modelEntityFactory = \Closure::fromCallable($modelEntityFactory);
        $this->modelRowFactory = \Closure::fromCallable($modelRowFactory);
        $this->modelRowsetFactory = \Closure::fromCallable($modelRowsetFactory);
        $this->modelRequestFactory = \Closure::fromCallable($modelRequestFactory);
    }

    public function entityDesignerFactory(callable $configuredServiceFactory): callable
    {
        return ($this->entityDesignerFactory)($configuredServiceFactory);
    }

    public function entityEncapsulantFactory(callable $configuredServiceFactory): callable
    {
        return ($this->entityEncapsulantFactory)($configuredServiceFactory);
    }

    public function modelEntityFactory(callable $configuredServiceFactory): callable
    {
        return ($this->modelEntityFactory)($configuredServiceFactory);
    }

    public function modelRowFactory(object $serializerOperations, callable $configuredServiceFactory): callable
    {
        return ($this->modelRowFactory)($serializerOperations, $configuredServiceFactory);
    }

    public function modelRowsetFactory(object $serializerOperations, callable $configuredServiceFactory): callable
    {
        return ($this->modelRowsetFactory)($serializerOperations, $configuredServiceFactory);
    }

    public function modelRequestFactory(callable $configuredServiceFactory): callable
    {
        return ($this->modelRequestFactory)($configuredServiceFactory);
    }
}
