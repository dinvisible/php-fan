<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_factory_dependencies
{
    private application_infrastructure_config_cache_config_factory_dependencies $config;
    private application_infrastructure_config_cache_cache_factory_dependencies $cache;

    public function __construct(container_interface $container)
    {
        $this->config = new application_infrastructure_config_cache_config_factory_dependencies($container);
        $this->cache = new application_infrastructure_config_cache_cache_factory_dependencies($container);
    }

    public function config(): mixed
    {
        return $this->config->config();
    }

    public function configFactory(): callable
    {
        return $this->config->configFactory();
    }

    public function configCacheFactory(): callable
    {
        return $this->cache->configCacheFactory();
    }

    public function cacheFactory(): callable
    {
        return $this->cache->cacheFactory();
    }
}
