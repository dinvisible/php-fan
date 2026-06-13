<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_core_fatal_exception_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function coreFatalExceptionFactory(): mixed
    {
        return $this->container->get(service_id::CORE_FATAL_EXCEPTION_FACTORY);
    }
}
