<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_request_payload_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function requestInput(): object
    {
        return $this->container->get(service_id::REQUEST_INPUT);
    }
}
