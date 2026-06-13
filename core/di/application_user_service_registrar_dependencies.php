<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_service_registrar_dependencies
{
    private application_user_state_registrar_dependencies $userState;
    private application_user_bootstrap_runtime_registrar_dependencies $bootstrapRuntime;
    private application_user_config_registrar_dependencies $config;
    private application_user_cache_factory_registrar_dependencies $cacheFactory;

    public function __construct(container_interface $container)
    {
        $this->userState = new application_user_state_registrar_dependencies($container);
        $this->bootstrapRuntime = new application_user_bootstrap_runtime_registrar_dependencies($container);
        $this->config = new application_user_config_registrar_dependencies($container);
        $this->cacheFactory = new application_user_cache_factory_registrar_dependencies($container);
    }

    public function userState(): object
    {
        return $this->userState->userState();
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
