<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_model_factory_defaults_provider
{
    private \Closure $modelFactoryProviderFactory;
    private \Closure $entityDesignerFactoryFactory;
    private \Closure $entityEncapsulantFactoryFactory;
    private \Closure $modelEntityFactoryFactory;
    private \Closure $modelRowFactoryFactory;
    private \Closure $modelRowsetFactoryFactory;
    private \Closure $modelRequestFactoryFactory;

    public function __construct(
        callable $modelFactoryProviderFactory,
        callable $entityDesignerFactoryFactory,
        callable $entityEncapsulantFactoryFactory,
        callable $modelEntityFactoryFactory,
        callable $modelRowFactoryFactory,
        callable $modelRowsetFactoryFactory,
        callable $modelRequestFactoryFactory
    ) {
        $this->modelFactoryProviderFactory = \Closure::fromCallable($modelFactoryProviderFactory);
        $this->entityDesignerFactoryFactory = \Closure::fromCallable($entityDesignerFactoryFactory);
        $this->entityEncapsulantFactoryFactory = \Closure::fromCallable($entityEncapsulantFactoryFactory);
        $this->modelEntityFactoryFactory = \Closure::fromCallable($modelEntityFactoryFactory);
        $this->modelRowFactoryFactory = \Closure::fromCallable($modelRowFactoryFactory);
        $this->modelRowsetFactoryFactory = \Closure::fromCallable($modelRowsetFactoryFactory);
        $this->modelRequestFactoryFactory = \Closure::fromCallable($modelRequestFactoryFactory);
    }

    public function applicationModelFactoryProvider(): application_model_factory_provider
    {
        $factory = $this->modelFactoryProviderFactory;

        return $factory(
            $this->entityDesignerFactoryFactory,
            $this->entityEncapsulantFactoryFactory,
            $this->modelEntityFactoryFactory,
            $this->modelRowFactoryFactory,
            $this->modelRowsetFactoryFactory,
            $this->modelRequestFactoryFactory
        );
    }
}
