<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_data_factory_group
{
    private base_dependency_data_core_factory_group $core;
    private base_dependency_data_loader_factory_group $loader;

    public function __construct(container_interface $container)
    {
        $this->core = new base_dependency_data_core_factory_group($container);
        $this->loader = new base_dependency_data_loader_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->core->dependencies(),
            $this->loader->dependencies()
        );
    }
}
