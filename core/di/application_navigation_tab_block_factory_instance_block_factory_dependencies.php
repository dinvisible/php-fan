<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_block_factory_instance_block_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function blockFactory(): mixed
    {
        return $this->container->get(service_id::BLOCK_FACTORY);
    }
}
