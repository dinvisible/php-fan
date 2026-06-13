<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_error_demonstrator_loader_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function errorDemonstratorLoader(): object
    {
        return $this->container->get(service_id::ERROR_DEMONSTRATOR_LOADER);
    }
}
