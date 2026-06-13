<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_config_cache_runtime_dependencies
{
    private application_client_config_runtime_dependencies $config;
    private application_client_cache_runtime_dependencies $cache;

    public function __construct(container_interface $container)
    {
        $this->config = new application_client_config_runtime_dependencies($container);
        $this->cache = new application_client_cache_runtime_dependencies($container);
    }

    public function config(): object
    {
        return $this->config->config();
    }

    public function cacheFactory(): callable
    {
        return $this->cache->cacheFactory();
    }
}
