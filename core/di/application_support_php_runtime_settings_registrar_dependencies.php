<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_php_runtime_settings_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function phpRuntimeSettings(): object
    {
        return $this->container->get(service_id::PHP_RUNTIME_SETTINGS);
    }
}
