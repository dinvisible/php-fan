<?php

declare(strict_types=1);

namespace fan\core\di;

final class timer_program_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(string $programClassName): object
    {
        return ($this->configuredServiceFactory)($programClassName, []);
    }
}
