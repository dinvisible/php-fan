<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\state;

final class bootstrap_state_defaults_factory
{
    private \Closure $stateFactory;

    public function __construct(?callable $stateFactory = null)
    {
        $this->stateFactory = \Closure::fromCallable(
            $stateFactory
                ?? static fn(): callable => static fn(): state => new state()
        );
    }

    public function stateFactory(): callable
    {
        return ($this->stateFactory)();
    }
}
