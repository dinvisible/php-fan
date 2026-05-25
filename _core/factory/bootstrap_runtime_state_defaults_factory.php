<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_runtime_state_defaults_factory
{
    private \Closure $runtimeStateFactory;

    public function __construct(callable $runtimeStateFactory)
    {
        $this->runtimeStateFactory = \Closure::fromCallable($runtimeStateFactory);
    }

    public function runtimeStateFactory(): callable
    {
        return ($this->runtimeStateFactory)();
    }
}
