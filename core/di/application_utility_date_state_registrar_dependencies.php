<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_date_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function dateState(): object
    {
        return $this->container->get(service_id::DATE_STATE);
    }
}
