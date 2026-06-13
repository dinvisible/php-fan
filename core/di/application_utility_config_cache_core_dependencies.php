<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_config_cache_core_dependencies
{
    private application_utility_config_config_cache_core_dependencies $config;
    private application_utility_cache_factory_config_cache_core_dependencies $cacheFactory;

    public function __construct(container_interface $container)
    {
        $this->config = new application_utility_config_config_cache_core_dependencies($container);
        $this->cacheFactory = new application_utility_cache_factory_config_cache_core_dependencies($container);
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
