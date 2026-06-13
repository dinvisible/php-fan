<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_reflector_factory_error_reflector_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function reflectorFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::REFLECTOR);
    }
}
