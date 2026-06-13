<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_service_single_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function serviceSingleState(): object
    {
        return $this->container->get(service_id::SERVICE_SINGLE_STATE);
    }
}
