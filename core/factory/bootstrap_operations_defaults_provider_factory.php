<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_operations_defaults_factory;
use fan\core\bootstrap\bootstrap_operations_factory;
use fan\core\bootstrap\context;
use fan\core\bootstrap\context_bootstrap_operations;

final class bootstrap_operations_defaults_provider_factory
{
    private \Closure $bootstrapOperationsFactoryFactory;
    private \Closure $contextBootstrapOperationsFactory;

    public function __construct(
        ?callable $bootstrapOperationsFactoryFactory = null,
        ?callable $contextBootstrapOperationsFactory = null
    )
    {
        $this->bootstrapOperationsFactoryFactory = \Closure::fromCallable(
            $bootstrapOperationsFactoryFactory
                ?? static fn(object $operations): bootstrap_operations_factory => new bootstrap_operations_factory($operations)
        );
        $this->contextBootstrapOperationsFactory = \Closure::fromCallable(
            $contextBootstrapOperationsFactory
                ?? static fn(context $context): context_bootstrap_operations => new context_bootstrap_operations($context)
        );
    }

    public function __invoke(): bootstrap_operations_defaults_factory
    {
        return new bootstrap_operations_defaults_factory(
            $this->bootstrapOperationsFactoryFactory,
            $this->contextBootstrapOperationsFactory
        );
    }
}
