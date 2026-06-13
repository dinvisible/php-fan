<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_array_runtime_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayAdducer(): mixed
    {
        return $this->container->get(service_id::ARRAY_ADDUCER);
    }
}
