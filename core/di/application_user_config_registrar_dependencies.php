<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_config_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function config(): object
    {
        return $this->container->get(service_id::CONFIG);
    }
}
