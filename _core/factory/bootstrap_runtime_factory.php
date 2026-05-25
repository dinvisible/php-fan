<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class bootstrap_runtime_factory
{
    private \Closure $bootstrapOperationsFactory;

    private \Closure $bootstrapRuntimeServiceFactory;

    private \Closure $bootstrapRuntimeStateFactory;

    public function __construct(
        callable $bootstrapOperationsFactory,
        callable $bootstrapRuntimeServiceFactory,
        callable $bootstrapRuntimeStateFactory
    ) {
        $this->bootstrapOperationsFactory = \Closure::fromCallable($bootstrapOperationsFactory);
        $this->bootstrapRuntimeServiceFactory = \Closure::fromCallable($bootstrapRuntimeServiceFactory);
        $this->bootstrapRuntimeStateFactory = \Closure::fromCallable($bootstrapRuntimeStateFactory);
    }

    public function __invoke(): object
    {
        $bootstrapOperations = ($this->bootstrapOperationsFactory)();
        if (!is_array($bootstrapOperations)) {
            throw new \RuntimeException('Bootstrap operations factory must return an array.');
        }

        $runtimeState = ($this->bootstrapRuntimeStateFactory)();
        if (!is_array($runtimeState)) {
            throw new \RuntimeException('Bootstrap runtime state factory must return an array.');
        }

        return ($this->bootstrapRuntimeServiceFactory)(
            $runtimeState['serviceListenerState'] ?? null,
            $runtimeState['serviceSingleState'] ?? null,
            $runtimeState['viewLoaderState'] ?? null,
            $runtimeState['metaMakerState'] ?? null,
            $runtimeState['specFileImageRowState'] ?? null,
            bootstrapOperations: $bootstrapOperations
        );
    }

}
