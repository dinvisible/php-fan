<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_view_router_factory_loader_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'viewRouterFactory' => $this->container->get('view_router_factory'),
        ];
    }
}
