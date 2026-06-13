<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_application_runtime_factory_group
{
    private base_dependency_application_service_runtime_factory_group $application;
    private base_dependency_config_application_runtime_factory_group $config;

    public function __construct(container_interface $container)
    {
        $this->application = new base_dependency_application_service_runtime_factory_group($container);
        $this->config = new base_dependency_config_application_runtime_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->application->dependencies(),
            $this->config->dependencies()
        );
    }
}
