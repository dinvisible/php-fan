<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_runtime_support_dependencies
{
    private application_infrastructure_config_cache_bootstrap_runtime_support_dependencies $bootstrapRuntime;
    private application_infrastructure_config_cache_error_factory_runtime_support_dependencies $errorFactory;

    public function __construct(container_interface $container)
    {
        $this->bootstrapRuntime = new application_infrastructure_config_cache_bootstrap_runtime_support_dependencies($container);
        $this->errorFactory = new application_infrastructure_config_cache_error_factory_runtime_support_dependencies($container);
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->bootstrapRuntime->bootstrapRuntime();
    }

    public function errorFactory(): callable
    {
        return $this->errorFactory->errorFactory();
    }
}
