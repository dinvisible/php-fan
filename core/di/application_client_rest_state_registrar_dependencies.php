<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_rest_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function restState(): object
    {
        return $this->container->get(service_id::REST_STATE);
    }
}
