<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_block_file_storage_group
{
    private base_dependency_block_file_storage_block_group $block;
    private base_dependency_block_file_storage_meta_group $meta;

    public function __construct(container_interface $container)
    {
        $this->block = new base_dependency_block_file_storage_block_group($container);
        $this->meta = new base_dependency_block_file_storage_meta_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->block->dependencies(),
            $this->meta->dependencies()
        );
    }
}
