<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_request_factory_runtime_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function requestFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::REQUEST);
    }
}
