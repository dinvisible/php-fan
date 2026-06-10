<?php

declare(strict_types=1);

namespace fan\core\di;

final class service_engine_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(string $class): object
    {
        return ($this->configuredServiceFactory)($class, []);
    }

}
