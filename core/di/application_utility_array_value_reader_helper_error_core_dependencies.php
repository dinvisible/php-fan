<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_array_value_reader_helper_error_core_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayValueReader(): callable
    {
        return $this->container->get(service_id::ARRAY_VALUE_READER);
    }
}
