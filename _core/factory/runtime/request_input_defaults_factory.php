<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class request_input_defaults_factory
{
    private \Closure $requestInputFactoryFactory;
    private \Closure $requestInputSourceFactory;

    public function __construct(callable $requestInputFactoryFactory, callable $requestInputSourceFactory)
    {
        $this->requestInputFactoryFactory = \Closure::fromCallable($requestInputFactoryFactory);
        $this->requestInputSourceFactory = \Closure::fromCallable($requestInputSourceFactory);
    }

    public function __invoke(): callable
    {
        return ($this->requestInputFactoryFactory)($this->requestInputSourceFactory);
    }
}
