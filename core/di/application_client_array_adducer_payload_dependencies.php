<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_array_adducer_payload_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayAdducer(): callable
    {
        return $this->container->get(service_id::ARRAY_ADDUCER);
    }
}
