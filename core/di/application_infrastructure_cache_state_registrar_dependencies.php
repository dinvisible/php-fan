<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_cache_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function cacheState(): object
    {
        return $this->container->get(service_id::CACHE_STATE);
    }
}
