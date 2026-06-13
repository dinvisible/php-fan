<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_serializer_operations_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function serializerOperations(): mixed
    {
        return $this->container->get(service_id::SERIALIZER_OPERATIONS);
    }
}
