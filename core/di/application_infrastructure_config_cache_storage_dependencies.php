<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_storage_dependencies
{
    private application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies $cacheSourceFileMetadata;
    private application_infrastructure_config_cache_config_source_file_storage_dependencies $configSourceFileStorage;

    public function __construct(container_interface $container)
    {
        $this->cacheSourceFileMetadata = new application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies($container);
        $this->configSourceFileStorage = new application_infrastructure_config_cache_config_source_file_storage_dependencies($container);
    }

    public function cacheSourceFileMetadata(): mixed
    {
        return $this->cacheSourceFileMetadata->cacheSourceFileMetadata();
    }

    public function configSourceFileStorage(): mixed
    {
        return $this->configSourceFileStorage->configSourceFileStorage();
    }
}
