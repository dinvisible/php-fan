<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_runtime_dependencies
{
    private application_user_bootstrap_cache_runtime_dependencies $bootstrapCache;
    private application_user_array_runtime_dependencies $array;

    public function __construct(container_interface $container)
    {
        $this->bootstrapCache = new application_user_bootstrap_cache_runtime_dependencies($container);
        $this->array = new application_user_array_runtime_dependencies($container);
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->bootstrapCache->bootstrapRuntime();
    }

    public function cacheFactory(): callable
    {
        return $this->bootstrapCache->cacheFactory();
    }

    public function arrayAdducer(): mixed
    {
        return $this->array->arrayAdducer();
    }
}
