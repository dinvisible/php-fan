<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_cache_factory_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function cacheFactory(): callable
    {
        return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);
    }
}
