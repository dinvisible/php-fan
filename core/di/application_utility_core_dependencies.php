<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_core_dependencies
{
    private application_utility_runtime_core_dependencies $runtime;
    private application_utility_config_cache_core_dependencies $configCache;
    private application_utility_helper_error_core_dependencies $helperError;

    public function __construct(container_interface $container)
    {
        $this->runtime = new application_utility_runtime_core_dependencies($container);
        $this->configCache = new application_utility_config_cache_core_dependencies($container);
        $this->helperError = new application_utility_helper_error_core_dependencies($container);
    }

    public function bootstrapRuntime(): object
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function config(): object
    {
        return $this->configCache->config();
    }

    public function cacheFactory(): callable
    {
        return $this->configCache->cacheFactory();
    }

    public function arrayValueReader(): callable
    {
        return $this->helperError->arrayValueReader();
    }

    public function errorFactory(): callable
    {
        return $this->helperError->errorFactory();
    }

    public function phpRuntimeSettings(): object
    {
        return $this->runtime->phpRuntimeSettings();
    }
}
