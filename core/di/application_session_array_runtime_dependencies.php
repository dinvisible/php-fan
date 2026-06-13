<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_array_runtime_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayValueReader(): callable
    {
        return $this->container->get(service_id::ARRAY_VALUE_READER);
    }
}
