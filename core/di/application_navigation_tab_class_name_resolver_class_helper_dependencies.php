<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_class_name_resolver_class_helper_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function classNameResolver(): mixed
    {
        return $this->container->get(service_id::CLASS_NAME_RESOLVER);
    }
}
