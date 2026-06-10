<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\context;

final class bootstrap_operations_defaults_factory
{
    private \Closure $bootstrapOperationsFactoryFactory;
    private \Closure $contextBootstrapOperationsFactory;

    public function __construct(callable $bootstrapOperationsFactoryFactory, callable $contextBootstrapOperationsFactory)
    {
        $this->bootstrapOperationsFactoryFactory = \Closure::fromCallable($bootstrapOperationsFactoryFactory);
        $this->contextBootstrapOperationsFactory = \Closure::fromCallable($contextBootstrapOperationsFactory);
    }

    public function operationsFactory(context $context): callable
    {
        return ($this->bootstrapOperationsFactoryFactory)(
            ($this->contextBootstrapOperationsFactory)($context)
        );
    }
}
