<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class request_input_source_defaults_factory
{
    private \Closure $sourceFactoryFactory;

    public function __construct(callable $sourceFactoryFactory)
    {
        $this->sourceFactoryFactory = \Closure::fromCallable($sourceFactoryFactory);
    }

    public function sourceFactory(): callable
    {
        return ($this->sourceFactoryFactory)();
    }
}
