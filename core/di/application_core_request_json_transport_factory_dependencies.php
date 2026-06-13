<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_json_transport_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function jsonFactory(): callable
    {
        return fn(bool $useBase64 = false): mixed => $this->container->get(service_id::JSON, $useBase64);
    }
}
