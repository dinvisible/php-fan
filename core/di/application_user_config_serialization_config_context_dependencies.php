<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_config_serialization_config_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function config(): mixed
    {
        return $this->container->get(service_id::CONFIG);
    }
}
