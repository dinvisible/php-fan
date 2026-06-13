<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_bootstrap_runtime_runtime_core_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function bootstrapRuntime(): object
    {
        return $this->container->get(service_id::BOOTSTRAP_RUNTIME);
    }
}
