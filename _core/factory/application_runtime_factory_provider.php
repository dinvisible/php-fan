<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_runtime_factory_provider
{
    private \Closure $configuredConstructionBoundary;

    private \Closure $requestInputFactory;

    private \Closure $phpArrayFileLoader;

    private \Closure $serializerOperationsFactory;

    public function __construct(
        callable $configuredConstructionBoundary,
        callable $requestInputFactory,
        callable $phpArrayFileLoader,
        callable $serializerOperationsFactory
    ) {
        $this->configuredConstructionBoundary = \Closure::fromCallable($configuredConstructionBoundary);
        $this->requestInputFactory = \Closure::fromCallable($requestInputFactory);
        $this->phpArrayFileLoader = \Closure::fromCallable($phpArrayFileLoader);
        $this->serializerOperationsFactory = \Closure::fromCallable($serializerOperationsFactory);
    }

    public function configuredConstructionBoundary(): callable
    {
        return $this->configuredConstructionBoundary;
    }

    public function requestInputFactory(): callable
    {
        return $this->requestInputFactory;
    }

    public function bootstrapOperationsFactory(): callable
    {
        return static fn(): array => [];
    }

    public function phpArrayFileLoader(): callable
    {
        return $this->phpArrayFileLoader;
    }

    public function serializerOperationsFactory(): callable
    {
        return $this->serializerOperationsFactory;
    }
}
