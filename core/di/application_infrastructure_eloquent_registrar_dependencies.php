<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_eloquent_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function eloquent(): object
    {
        return $this->container->get(service_id::ELOQUENT);
    }
}
