<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_container_defaults_factory
{
    private \Closure $containerFactory;

    public function __construct(callable $containerFactory)
    {
        $this->containerFactory = \Closure::fromCallable($containerFactory);
    }

    public function containerFactory(): callable
    {
        return ($this->containerFactory)();
    }
}
