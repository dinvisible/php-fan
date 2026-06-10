<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_runtime_factory_defaults_provider
{
    private \Closure $runtimeFactoryProviderFactory;
    private \Closure $configuredConstructionBoundary;
    private \Closure $requestInputFactory;
    private \Closure $phpArrayFileLoader;
    private \Closure $serializerOperationsFactory;

    public function __construct(
        callable $runtimeFactoryProviderFactory,
        callable $configuredConstructionBoundary,
        callable $requestInputFactory,
        callable $phpArrayFileLoader,
        callable $serializerOperationsFactory
    ) {
        $this->runtimeFactoryProviderFactory = \Closure::fromCallable($runtimeFactoryProviderFactory);
        $this->configuredConstructionBoundary = \Closure::fromCallable($configuredConstructionBoundary);
        $this->requestInputFactory = \Closure::fromCallable($requestInputFactory);
        $this->phpArrayFileLoader = \Closure::fromCallable($phpArrayFileLoader);
        $this->serializerOperationsFactory = \Closure::fromCallable($serializerOperationsFactory);
    }

    public function applicationRuntimeFactoryProvider(): application_runtime_factory_provider
    {
        $factory = $this->runtimeFactoryProviderFactory;

        return $factory(
            $this->configuredConstructionBoundary,
            $this->requestInputFactory,
            $this->phpArrayFileLoader,
            $this->serializerOperationsFactory
        );
    }
}
