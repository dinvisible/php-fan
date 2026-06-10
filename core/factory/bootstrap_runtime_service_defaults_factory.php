<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_runtime_service_defaults_factory
{
    private \Closure $runtimeServiceFactory;

    public function __construct(callable $runtimeServiceFactory)
    {
        $this->runtimeServiceFactory = \Closure::fromCallable($runtimeServiceFactory);
    }

    public function runtimeServiceFactory(): callable
    {
        return ($this->runtimeServiceFactory)();
    }
}
