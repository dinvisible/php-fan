<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_request_input_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function requestInput(): mixed
    {
        return $this->container->get(service_id::REQUEST_INPUT);
    }
}
