<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_dependencies
{
    private application_infrastructure_config_cache_factory_dependencies $factory;
    private application_infrastructure_config_cache_support_dependencies $support;
    private application_infrastructure_config_cache_storage_dependencies $storage;
    private application_infrastructure_config_cache_exception_dependencies $exception;

    public function __construct(container_interface $container)
    {
        $this->factory = new application_infrastructure_config_cache_factory_dependencies($container);
        $this->support = new application_infrastructure_config_cache_support_dependencies($container);
        $this->storage = new application_infrastructure_config_cache_storage_dependencies($container);
        $this->exception = new application_infrastructure_config_cache_exception_dependencies($container);
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->support->bootstrapRuntime();
    }

    public function config(): mixed
    {
        return $this->factory->config();
    }

    public function configFactory(): callable
    {
        return $this->factory->configFactory();
    }

    public function configCacheFactory(): callable
    {
        return $this->factory->configCacheFactory();
    }

    public function cacheFactory(): callable
    {
        return $this->factory->cacheFactory();
    }

    public function errorFactory(): callable
    {
        return $this->support->errorFactory();
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->support->phpArrayFileLoader();
    }

    public function serializerOperations(): mixed
    {
        return $this->support->serializerOperations();
    }

    public function shortClassNameResolver(): mixed
    {
        return $this->support->shortClassNameResolver();
    }

    public function cacheSourceFileMetadata(): mixed
    {
        return $this->storage->cacheSourceFileMetadata();
    }

    public function configSourceFileStorage(): mixed
    {
        return $this->storage->configSourceFileStorage();
    }

    public function coreFatalExceptionFactory(): mixed
    {
        return $this->exception->coreFatalExceptionFactory();
    }

    public function requestInput(): mixed
    {
        return $this->exception->requestInput();
    }

    public function headerWriter(): mixed
    {
        return $this->exception->headerWriter();
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->exception->error500ExceptionFactory();
    }
}
