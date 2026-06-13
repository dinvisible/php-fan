<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_application_factory_application_request_input_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function applicationFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::APPLICATION);
    }
}
