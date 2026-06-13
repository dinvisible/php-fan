<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_application_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function application(): object
    {
        return $this->container->get(service_id::APPLICATION);
    }
}
