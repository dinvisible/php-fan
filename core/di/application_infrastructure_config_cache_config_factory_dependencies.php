<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_config_factory_dependencies
{
    private application_infrastructure_config_cache_config_instance_dependencies $config;
    private application_infrastructure_config_cache_typed_config_factory_dependencies $configFactory;

    public function __construct(container_interface $container)
    {
        $this->config = new application_infrastructure_config_cache_config_instance_dependencies($container);
        $this->configFactory = new application_infrastructure_config_cache_typed_config_factory_dependencies($container);
    }

    public function config(): mixed
    {
        return $this->config->config();
    }

    public function configFactory(): callable
    {
        return $this->configFactory->configFactory();
    }
}
