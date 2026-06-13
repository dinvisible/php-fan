<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_runtime_bootstrap_runtime_error_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function bootstrapRuntime(): object
    {
        return $this->container->get(service_id::BOOTSTRAP_RUNTIME);
    }
}
