<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function cacheSourceFileMetadata(): mixed
    {
        return $this->container->get(service_id::CACHE_SOURCE_FILE_METADATA);
    }
}
