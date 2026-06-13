<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_service_registrar_dependencies
{
    private application_infrastructure_config_state_registrar_dependencies $configState;
    private application_infrastructure_cache_state_registrar_dependencies $cacheState;
    private application_infrastructure_cache_memcache_state_registrar_dependencies $cacheMemcacheState;
    private application_infrastructure_json_state_registrar_dependencies $jsonState;
    private application_infrastructure_file_system_state_registrar_dependencies $fileSystemState;
    private application_infrastructure_bootstrap_runtime_registrar_dependencies $bootstrapRuntime;
    private application_infrastructure_config_registrar_dependencies $config;
    private application_infrastructure_cache_factory_registrar_dependencies $cacheFactory;

    public function __construct(container_interface $container)
    {
        $this->configState = new application_infrastructure_config_state_registrar_dependencies($container);
        $this->cacheState = new application_infrastructure_cache_state_registrar_dependencies($container);
        $this->cacheMemcacheState = new application_infrastructure_cache_memcache_state_registrar_dependencies($container);
        $this->jsonState = new application_infrastructure_json_state_registrar_dependencies($container);
        $this->fileSystemState = new application_infrastructure_file_system_state_registrar_dependencies($container);
        $this->bootstrapRuntime = new application_infrastructure_bootstrap_runtime_registrar_dependencies($container);
        $this->config = new application_infrastructure_config_registrar_dependencies($container);
        $this->cacheFactory = new application_infrastructure_cache_factory_registrar_dependencies($container);
    }

    public function configState(): object
    {
        return $this->configState->configState();
    }

    public function cacheState(): object
    {
        return $this->cacheState->cacheState();
    }

    public function cacheMemcacheState(): object
    {
        return $this->cacheMemcacheState->cacheMemcacheState();
    }

    public function jsonState(): object
    {
        return $this->jsonState->jsonState();
    }

    public function fileSystemState(): object
    {
        return $this->fileSystemState->fileSystemState();
    }

    public function bootstrapRuntime(): object
    {
        return $this->bootstrapRuntime->bootstrapRuntime();
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
