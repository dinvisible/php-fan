<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_meta_maker_group
{
    private base_dependency_meta_maker_state_group $state;
    private base_dependency_meta_maker_factory_group $factory;

    public function __construct(container_interface $container)
    {
        $this->state = new base_dependency_meta_maker_state_group($container);
        $this->factory = new base_dependency_meta_maker_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->state->dependencies(),
            $this->factory->dependencies()
        );
    }
}
