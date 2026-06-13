<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_array_value_reader_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayValueReader(): mixed
    {
        return $this->container->get(service_id::ARRAY_VALUE_READER);
    }
}
