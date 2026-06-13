<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_meta_file_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function metaFileStorage(): object
    {
        return $this->container->get(service_id::META_FILE_STORAGE);
    }
}
