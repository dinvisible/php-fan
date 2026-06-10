<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_object_defaults_factory
{
    private \Closure $objectFactoryFactory;

    public function __construct(callable $objectFactoryFactory)
    {
        $this->objectFactoryFactory = \Closure::fromCallable($objectFactoryFactory);
    }

    public function objectFactory(): callable
    {
        return ($this->objectFactoryFactory)();
    }
}
