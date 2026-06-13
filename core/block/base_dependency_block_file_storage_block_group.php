<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_block_file_storage_block_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'blockFileStorage' => $this->container->has('block_file_storage') ? $this->container->get('block_file_storage') : null,
        ];
    }
}
