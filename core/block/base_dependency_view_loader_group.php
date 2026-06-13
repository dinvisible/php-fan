<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_view_loader_group
{
    private base_dependency_view_factory_loader_group $factory;
    private base_dependency_view_state_loader_group $state;

    public function __construct(container_interface $container)
    {
        $this->factory = new base_dependency_view_factory_loader_group($container);
        $this->state = new base_dependency_view_state_loader_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->factory->dependencies(),
            $this->state->dependencies()
        );
    }
}
