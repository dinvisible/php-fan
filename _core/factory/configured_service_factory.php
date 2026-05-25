<?php

declare(strict_types=1);

namespace fan\core\di;

final class configured_service_factory
{
    private \Closure $classInstantiator;

    public function __construct(callable $classInstantiator)
    {
        $this->classInstantiator = \Closure::fromCallable($classInstantiator);
    }

    public function __invoke(string $className, array $arguments): object
    {
        $service = ($this->classInstantiator)($className, $arguments);
        if (!is_object($service)) {
            throw new \RuntimeException('Configured class instantiator must return an object.');
        }

        return $service;
    }
}
