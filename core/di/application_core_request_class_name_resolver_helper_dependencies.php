<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_class_name_resolver_helper_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function classNameResolver(): callable
    {
        return $this->container->get(service_id::CLASS_NAME_RESOLVER);
    }
}
