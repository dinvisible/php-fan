<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_data_loader_service_factory_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'dataLoaderFactory' => fn(): mixed => $this->container->get('data_loader'),
        ];
    }
}
