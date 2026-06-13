<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_cache_factory_dependencies
{
    private application_infrastructure_config_cache_config_cache_factory_dependencies $configCacheFactory;
    private application_infrastructure_config_cache_type_cache_factory_dependencies $cacheFactory;

    public function __construct(container_interface $container)
    {
        $this->configCacheFactory = new application_infrastructure_config_cache_config_cache_factory_dependencies($container);
        $this->cacheFactory = new application_infrastructure_config_cache_type_cache_factory_dependencies($container);
    }

    public function configCacheFactory(): callable
    {
        return $this->configCacheFactory->configCacheFactory();
    }

    public function cacheFactory(): callable
    {
        return $this->cacheFactory->cacheFactory();
    }
}
