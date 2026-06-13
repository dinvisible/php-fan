<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_meta_loader_group
{
    private base_dependency_meta_maker_group $maker;
    private base_dependency_meta_row_loader_group $rowLoader;

    public function __construct(container_interface $container)
    {
        $this->maker = new base_dependency_meta_maker_group($container);
        $this->rowLoader = new base_dependency_meta_row_loader_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->maker->dependencies(),
            $this->rowLoader->dependencies()
        );
    }
}
