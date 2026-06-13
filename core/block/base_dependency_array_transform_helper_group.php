<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_array_transform_helper_group
{
    private base_dependency_array_adducer_helper_group $adducer;
    private base_dependency_recursive_merger_helper_group $merger;

    public function __construct(container_interface $container)
    {
        $this->adducer = new base_dependency_array_adducer_helper_group($container);
        $this->merger = new base_dependency_recursive_merger_helper_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->adducer->dependencies(),
            $this->merger->dependencies()
        );
    }
}
