<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_runtime_service_factory
{
    private \Closure $runtimeFactory;

    public function __construct(callable $runtimeFactory)
    {
        $this->runtimeFactory = \Closure::fromCallable($runtimeFactory);
    }

    public function __invoke(
        ?object $serviceListenerState = null,
        ?object $serviceSingleState = null,
        ?object $viewLoaderState = null,
        ?object $metaMakerState = null,
        ?object $specFileImageRowState = null,
        ?callable $serviceEngineFactory = null,
        array $bootstrapOperations = [],
        ?callable $serviceExceptionFactory = null,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null
    ): object {
        return ($this->runtimeFactory)(
            $serviceListenerState,
            $serviceSingleState,
            $viewLoaderState,
            $metaMakerState,
            $specFileImageRowState,
            $serviceEngineFactory,
            $bootstrapOperations,
            $serviceExceptionFactory,
            $classNameResolver,
            $arrayValueReader
        );
    }
}
