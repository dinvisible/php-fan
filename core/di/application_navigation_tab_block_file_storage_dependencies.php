<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_block_file_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function blockFileStorage(): mixed
    {
        return $this->container->get(service_id::BLOCK_FILE_STORAGE);
    }
}
