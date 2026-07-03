<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\database_connections;

final class application_infrastructure_database_connections_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function databaseConnections(): database_connections
    {
        $connections = $this->container->get(service_id::DATABASE_CONNECTIONS);
        if (!$connections instanceof database_connections) {
            throw new \UnexpectedValueException('Database connections service has an invalid type.');
        }

        return $connections;
    }
}
