<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_config_cache_runtime_dependencies
{
    private application_pager_config_config_cache_runtime_dependencies $config;
    private application_pager_cache_factory_config_cache_runtime_dependencies $cacheFactory;

    public function __construct(container_interface $container)
    {
        $this->config = new application_pager_config_config_cache_runtime_dependencies($container);
        $this->cacheFactory = new application_pager_cache_factory_config_cache_runtime_dependencies($container);
    }

    public function config(): object
    {
        return $this->config->config();
    }

    public function cacheFactory(): callable
    {
        return $this->cacheFactory->cacheFactory();
    }
}
