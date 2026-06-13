<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_array_helper_group
{
    private base_dependency_array_transform_helper_group $transform;
    private base_dependency_array_read_helper_group $read;

    public function __construct(container_interface $container)
    {
        $this->transform = new base_dependency_array_transform_helper_group($container);
        $this->read = new base_dependency_array_read_helper_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->transform->dependencies(),
            $this->read->dependencies()
        );
    }
}
