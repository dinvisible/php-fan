<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_object_configured_service_provider
{
    public function __invoke(callable $classInstantiator): callable
    {
        return new configured_service_factory($classInstantiator);
    }
}
