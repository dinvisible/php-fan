<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_header_factory_config_header_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function headerFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::HEADER);
    }
}
