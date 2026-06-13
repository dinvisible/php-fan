<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_role_factory_role_transfer_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function roleFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::ROLE);
    }
}
