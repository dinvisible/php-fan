<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_creator_common_dependencies
{
    private application_creator_bootstrap_runtime_dependencies $bootstrap;
    private application_creator_config_cache_dependencies $configCache;

    public function __construct(container_interface $container)
    {
        $this->bootstrap = new application_creator_bootstrap_runtime_dependencies($container);
        $this->configCache = new application_creator_config_cache_dependencies($container);
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->bootstrap->bootstrapRuntime();
    }

    public function config(): mixed
    {
        return $this->configCache->config();
    }

    public function cacheFactory(): callable
    {
        return $this->configCache->cacheFactory();
    }
}
