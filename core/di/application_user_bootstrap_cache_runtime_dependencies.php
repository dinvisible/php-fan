<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_bootstrap_cache_runtime_dependencies
{
    private application_user_bootstrap_runtime_bootstrap_cache_runtime_dependencies $bootstrapRuntime;
    private application_user_cache_factory_bootstrap_cache_runtime_dependencies $cacheFactory;

    public function __construct(container_interface $container)
    {
        $this->bootstrapRuntime = new application_user_bootstrap_runtime_bootstrap_cache_runtime_dependencies($container);
        $this->cacheFactory = new application_user_cache_factory_bootstrap_cache_runtime_dependencies($container);
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->bootstrapRuntime->bootstrapRuntime();
    }

    public function cacheFactory(): callable
    {
        return $this->cacheFactory->cacheFactory();
    }
}
