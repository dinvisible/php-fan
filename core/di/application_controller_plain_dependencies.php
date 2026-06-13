<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_plain_dependencies
{
    private application_controller_plain_route_dependencies $route;
    private application_controller_plain_config_dependencies $config;

    public function __construct(container_interface $container)
    {
        $this->route = new application_controller_plain_route_dependencies($container);
        $this->config = new application_controller_plain_config_dependencies($container);
    }

    public function matcher(): object
    {
        return $this->route->matcher();
    }

    public function plainConfigFactory(): callable
    {
        return $this->config->plainConfigFactory();
    }

    public function header(): object
    {
        return $this->route->header();
    }
}
