<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_block_file_storage_meta_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'metaFileStorage' => $this->container->has('meta_file_storage') ? $this->container->get('meta_file_storage') : null,
        ];
    }
}
