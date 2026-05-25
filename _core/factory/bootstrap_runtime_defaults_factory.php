<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\context;
use fan\core\bootstrap\bootstrap_runtime_factory;

final class bootstrap_runtime_defaults_factory
{
    private \Closure $bootstrapRuntimeFactoryFactory;
    private \Closure $bootstrapOperationsFactoryFactory;
    private \Closure $bootstrapRuntimeServiceFactory;
    private \Closure $bootstrapRuntimeStateFactory;

    public function __construct(
        ?callable $bootstrapRuntimeFactoryFactory = null,
        ?callable $bootstrapOperationsFactoryFactory = null,
        ?callable $bootstrapRuntimeServiceFactory = null,
        ?callable $bootstrapRuntimeStateFactory = null
    ) {
        $this->bootstrapRuntimeFactoryFactory = \Closure::fromCallable(
            $bootstrapRuntimeFactoryFactory
                ?? static fn(
                    callable $bootstrapOperationsFactory,
                    callable $bootstrapRuntimeServiceFactory,
                    callable $bootstrapRuntimeStateFactory
                ): bootstrap_runtime_factory => new bootstrap_runtime_factory(
                    $bootstrapOperationsFactory,
                    $bootstrapRuntimeServiceFactory,
                    $bootstrapRuntimeStateFactory
                )
        );
        $this->bootstrapOperationsFactoryFactory = \Closure::fromCallable(
            $bootstrapOperationsFactoryFactory
                ?? (new bootstrap_runtime_operations_defaults_provider_factory())()
        );
        $this->bootstrapRuntimeServiceFactory = \Closure::fromCallable(
            $bootstrapRuntimeServiceFactory
                ?? (new bootstrap_runtime_service_factory_defaults_provider_factory())()
        );
        $this->bootstrapRuntimeStateFactory = \Closure::fromCallable(
            $bootstrapRuntimeStateFactory
                ?? (new bootstrap_runtime_state_factory_defaults_provider_factory())()
        );
    }

    public function __invoke(): callable
    {
        return function (context $context): object {
            $factory = ($this->bootstrapRuntimeFactoryFactory)(
                ($this->bootstrapOperationsFactoryFactory)($context),
                $this->bootstrapRuntimeServiceFactory,
                $this->bootstrapRuntimeStateFactory
            );

            return $factory();
        };
    }
}
