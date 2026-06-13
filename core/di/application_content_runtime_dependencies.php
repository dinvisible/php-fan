<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_runtime_dependencies
{
    private application_content_bootstrap_runtime_dependencies $bootstrap;
    private application_content_config_cache_runtime_dependencies $configCache;

    public function __construct(container_interface $container)
    {
        $this->bootstrap = new application_content_bootstrap_runtime_dependencies($container);
        $this->configCache = new application_content_config_cache_runtime_dependencies($container);
    }

    public function bootstrapRuntime(): object
    {
        return $this->bootstrap->bootstrapRuntime();
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
