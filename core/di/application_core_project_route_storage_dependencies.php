<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_route_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function matcherRouteFileStorage(): object
    {
        return $this->container->get(service_id::MATCHER_ROUTE_FILE_STORAGE);
    }
}
