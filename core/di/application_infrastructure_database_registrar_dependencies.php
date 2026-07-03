<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_database_registrar_dependencies
{
    private application_infrastructure_eloquent_registrar_dependencies $eloquent;
    private application_infrastructure_database_config_registrar_dependencies $config;
    private application_infrastructure_database_connections_registrar_dependencies $connections;

    public function __construct(container_interface $container)
    {
        $this->eloquent = new application_infrastructure_eloquent_registrar_dependencies($container);
        $this->config = new application_infrastructure_database_config_registrar_dependencies($container);
        $this->connections = new application_infrastructure_database_connections_registrar_dependencies($container);
    }

    public function eloquent(): object
    {
        return $this->eloquent->eloquent();
    }

    public function databaseConfig(): array|object
    {
        return $this->config->databaseConfig();
    }

    public function databaseConnections(): \fan\core\service\database_connections
    {
        return $this->connections->databaseConnections();
    }
}
