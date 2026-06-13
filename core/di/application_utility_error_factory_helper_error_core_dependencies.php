<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_error_factory_helper_error_core_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function errorFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::ERROR);
    }
}
