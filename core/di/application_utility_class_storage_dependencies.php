<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_class_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function classNameResolver(): callable
    {
        return $this->container->get(service_id::CLASS_NAME_RESOLVER);
    }
}
