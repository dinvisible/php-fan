<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function fileSystemStorage(): object
    {
        return $this->container->get(service_id::FILE_SYSTEM_STORAGE);
    }
}
