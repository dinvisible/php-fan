<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_support_dependencies
{
    private application_infrastructure_config_cache_runtime_support_dependencies $runtime;
    private application_infrastructure_config_cache_loader_serializer_dependencies $loaderSerializer;
    private application_infrastructure_config_cache_class_helper_dependencies $classHelper;

    public function __construct(container_interface $container)
    {
        $this->runtime = new application_infrastructure_config_cache_runtime_support_dependencies($container);
        $this->loaderSerializer = new application_infrastructure_config_cache_loader_serializer_dependencies($container);
        $this->classHelper = new application_infrastructure_config_cache_class_helper_dependencies($container);
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function errorFactory(): callable
    {
        return $this->runtime->errorFactory();
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->loaderSerializer->phpArrayFileLoader();
    }

    public function serializerOperations(): mixed
    {
        return $this->loaderSerializer->serializerOperations();
    }

    public function shortClassNameResolver(): mixed
    {
        return $this->classHelper->shortClassNameResolver();
    }
}
