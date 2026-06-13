<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_config_source_file_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function configSourceFileStorage(): mixed
    {
        return $this->container->get(service_id::CONFIG_SOURCE_FILE_STORAGE);
    }
}
