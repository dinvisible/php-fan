<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_class_name_resolver_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function classNameResolver(): mixed
    {
        return $this->container->get(service_id::CLASS_NAME_RESOLVER);
    }
}
