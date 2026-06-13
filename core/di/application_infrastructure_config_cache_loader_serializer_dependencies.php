<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_loader_serializer_dependencies
{
    private application_infrastructure_config_cache_php_array_file_loader_dependencies $phpArrayFileLoader;
    private application_infrastructure_config_cache_serializer_operations_dependencies $serializerOperations;

    public function __construct(container_interface $container)
    {
        $this->phpArrayFileLoader = new application_infrastructure_config_cache_php_array_file_loader_dependencies($container);
        $this->serializerOperations = new application_infrastructure_config_cache_serializer_operations_dependencies($container);
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->phpArrayFileLoader->phpArrayFileLoader();
    }

    public function serializerOperations(): mixed
    {
        return $this->serializerOperations->serializerOperations();
    }
}
