<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_application_factory_group
{
    private base_dependency_application_runtime_factory_group $runtime;
    private base_dependency_application_data_factory_group $data;
    private base_dependency_application_support_factory_group $support;

    public function __construct(container_interface $container)
    {
        $this->runtime = new base_dependency_application_runtime_factory_group($container);
        $this->data = new base_dependency_application_data_factory_group($container);
        $this->support = new base_dependency_application_support_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->runtime->dependencies(),
            $this->data->dependencies(),
            $this->support->dependencies()
        );
    }
}
