<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_error_demonstrator_file_storage_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function errorDemonstratorFileStorage(): object
    {
        return $this->container->get(service_id::ERROR_DEMONSTRATOR_FILE_STORAGE);
    }
}
