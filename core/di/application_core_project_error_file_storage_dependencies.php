<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_error_file_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function errorFileStorage(): object
    {
        return $this->container->get(service_id::ERROR_FILE_STORAGE);
    }
}
