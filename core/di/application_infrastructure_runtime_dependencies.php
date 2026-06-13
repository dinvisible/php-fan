<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_runtime_dependencies
{
    private application_infrastructure_runtime_error_dependencies $runtimeError;
    private application_infrastructure_runtime_config_cache_dependencies $configCache;

    public function __construct(container_interface $container)
    {
        $this->runtimeError = new application_infrastructure_runtime_error_dependencies($container);
        $this->configCache = new application_infrastructure_runtime_config_cache_dependencies($container);
    }

    public function errorFactory(): callable
    {
        return $this->runtimeError->errorFactory();
    }

    public function bootstrapRuntime(): object
    {
        return $this->runtimeError->bootstrapRuntime();
    }

    public function config(): object
    {
        return $this->configCache->config();
    }

    public function cacheFactory(): callable
    {
        return $this->configCache->cacheFactory();
    }
}
