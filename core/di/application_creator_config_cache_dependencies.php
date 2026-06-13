<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_creator_config_cache_dependencies
{
    private application_creator_config_dependencies $config;
    private application_creator_cache_factory_dependencies $cacheFactory;

    public function __construct(container_interface $container)
    {
        $this->config = new application_creator_config_dependencies($container);
        $this->cacheFactory = new application_creator_cache_factory_dependencies($container);
    }

    public function config(): mixed
    {
        return $this->config->config();
    }

    public function cacheFactory(): callable
    {
        return $this->cacheFactory->cacheFactory();
    }
}
