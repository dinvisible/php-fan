<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_error_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function error(): object
    {
        return $this->container->get(service_id::ERROR);
    }
}
