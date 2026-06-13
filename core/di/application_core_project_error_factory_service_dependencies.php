<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_error_factory_service_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function errorFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::ERROR);
    }
}
