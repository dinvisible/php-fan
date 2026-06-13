<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_plain_file_storage_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function plainFileStorage(): object
    {
        return $this->container->get(service_id::PLAIN_FILE_STORAGE);
    }
}
