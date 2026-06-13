<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_service_dependencies_registrar_dependencies
{
    private application_support_bootstrap_runtime_registrar_dependencies $bootstrapRuntime;
    private application_support_config_registrar_dependencies $config;
    private application_support_cache_factory_registrar_dependencies $cacheFactory;
    private application_support_class_name_resolver_registrar_dependencies $classNameResolver;
    private application_support_array_value_reader_registrar_dependencies $arrayValueReader;

    public function __construct(container_interface $container)
    {
        $this->bootstrapRuntime = new application_support_bootstrap_runtime_registrar_dependencies($container);
        $this->config = new application_support_config_registrar_dependencies($container);
        $this->cacheFactory = new application_support_cache_factory_registrar_dependencies($container);
        $this->classNameResolver = new application_support_class_name_resolver_registrar_dependencies($container);
        $this->arrayValueReader = new application_support_array_value_reader_registrar_dependencies($container);
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

    public function serviceEngineFactory(): mixed
    {
        return $this->bootstrapRuntime()->serviceEngineFactory();
    }

    public function serviceExceptionFactory(): mixed
    {
        return $this->bootstrapRuntime()->serviceExceptionFactory();
    }

    public function classNameResolver(): mixed
    {
        return $this->classNameResolver->classNameResolver();
    }

    public function arrayValueReader(): mixed
    {
        return $this->arrayValueReader->arrayValueReader();
    }
}
