<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_typed_config_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function configFactory(): callable
    {
        return fn(string $configType = 'service', string $sourceType = 'arr'): mixed => $this->container->get(service_id::CONFIG, $configType, $sourceType);
    }
}
