<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_runtime_dependencies
{
    private application_pager_bootstrap_runtime_dependencies $bootstrap;
    private application_pager_config_cache_runtime_dependencies $configCache;

    public function __construct(container_interface $container)
    {
        $this->bootstrap = new application_pager_bootstrap_runtime_dependencies($container);
        $this->configCache = new application_pager_config_cache_runtime_dependencies($container);
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
