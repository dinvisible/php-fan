<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_request_handler_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function request(): object
    {
        return $this->container->get(service_id::REQUEST);
    }
}
