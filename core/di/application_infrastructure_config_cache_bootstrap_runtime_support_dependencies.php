<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_bootstrap_runtime_support_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->container->get(service_id::BOOTSTRAP_RUNTIME);
    }
}
