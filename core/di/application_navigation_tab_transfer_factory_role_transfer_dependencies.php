<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_transfer_factory_role_transfer_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function transferFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::TRANSFER);
    }
}
