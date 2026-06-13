<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_view_meta_group
{
    private base_dependency_view_loader_group $view;
    private base_dependency_meta_loader_group $meta;
    private base_dependency_block_factory_context_group $blockFactory;

    public function __construct(container_interface $container)
    {
        $this->view = new base_dependency_view_loader_group($container);
        $this->meta = new base_dependency_meta_loader_group($container);
        $this->blockFactory = new base_dependency_block_factory_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->view->dependencies(),
            $this->meta->dependencies(),
            $this->blockFactory->dependencies()
        );
    }
}
