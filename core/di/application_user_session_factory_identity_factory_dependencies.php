<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_session_factory_identity_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function sessionFactory(): callable
    {
        return fn(string $namespace, string $group): mixed => $this->container->get(service_id::SESSION, $namespace, $group);
    }
}
