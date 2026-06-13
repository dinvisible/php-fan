<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_data_loader_factory_group
{
    private base_dependency_data_loader_service_factory_group $dataLoader;
    private base_dependency_pager_data_loader_factory_group $pager;

    public function __construct(container_interface $container)
    {
        $this->dataLoader = new base_dependency_data_loader_service_factory_group($container);
        $this->pager = new base_dependency_pager_data_loader_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->dataLoader->dependencies(),
            $this->pager->dependencies()
        );
    }
}
