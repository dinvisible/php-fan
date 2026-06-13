<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_serializer_operations_payload_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function serializerOperations(): object
    {
        return $this->container->get(service_id::SERIALIZER_OPERATIONS);
    }
}
