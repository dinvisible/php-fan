<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_database_connections_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function databaseConnections(): object
    {
        return $this->container->get(service_id::DATABASE_CONNECTIONS);
    }
}
