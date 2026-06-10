<?php

declare(strict_types=1);

namespace fan\core\di;

final class entity_encapsulant_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(string $encapsulantClass, object $entityService): object
    {
        return ($this->configuredServiceFactory)($encapsulantClass, [$entityService]);
    }

}
