<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_request_instance_request_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function request(): mixed
    {
        return $this->container->get(service_id::REQUEST);
    }
}
