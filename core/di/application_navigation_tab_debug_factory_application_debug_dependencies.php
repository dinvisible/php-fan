<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_debug_factory_application_debug_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function debugFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::DEBUG);
    }
}
