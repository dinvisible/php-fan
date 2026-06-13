<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_php_runtime_settings_runtime_core_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function phpRuntimeSettings(): object
    {
        return $this->container->get(service_id::PHP_RUNTIME_SETTINGS);
    }
}
