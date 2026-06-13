<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_cache_factory_config_cache_core_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function cacheFactory(): callable
    {
        return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);
    }
}
