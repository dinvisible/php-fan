<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\context;

final class bootstrap_runtime_operations_defaults_provider_factory
{
    private \Closure $bootstrapOperationsDefaultsProviderFactoryProvider;

    public function __construct(?callable $bootstrapOperationsDefaultsProviderFactoryProvider = null)
    {
        $this->bootstrapOperationsDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $bootstrapOperationsDefaultsProviderFactoryProvider
                ?? static fn(): callable => new bootstrap_operations_defaults_provider_factory()
        );
    }

    public function __invoke(): callable
    {
        $bootstrapOperationsDefaultsProviderFactory = ($this->bootstrapOperationsDefaultsProviderFactoryProvider)();

        return static fn(context $context): callable => $bootstrapOperationsDefaultsProviderFactory()
            ->operationsFactory($context);
    }
}
