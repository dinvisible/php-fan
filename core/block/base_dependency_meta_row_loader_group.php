<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_meta_row_loader_group
{
    private base_dependency_php_array_file_loader_group $phpArrayFileLoader;
    private base_dependency_meta_row_factory_group $metaRowFactory;

    public function __construct(container_interface $container)
    {
        $this->phpArrayFileLoader = new base_dependency_php_array_file_loader_group($container);
        $this->metaRowFactory = new base_dependency_meta_row_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->phpArrayFileLoader->dependencies(),
            $this->metaRowFactory->dependencies()
        );
    }
}
