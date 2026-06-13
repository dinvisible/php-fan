<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_tab_state_block_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function tabState(): mixed
    {
        return $this->container->get(service_id::TAB_STATE);
    }
}
