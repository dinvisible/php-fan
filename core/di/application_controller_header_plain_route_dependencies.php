<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_header_plain_route_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function header(): object
    {
        return $this->container->get(service_id::HEADER);
    }
}
