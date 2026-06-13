<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_session_exception_session_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function session(string $namespace, string $group): mixed
    {
        return $this->container->get(service_id::SESSION, $namespace, $group);
    }
}
