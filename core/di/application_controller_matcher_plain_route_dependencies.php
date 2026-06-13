<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_matcher_plain_route_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function matcher(): object
    {
        return $this->container->get(service_id::MATCHER);
    }
}
