<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_php_runtime_settings_bootstrap_runtime_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function phpRuntimeSettings(): mixed
    {
        return $this->container->get(service_id::PHP_RUNTIME_SETTINGS);
    }
}
