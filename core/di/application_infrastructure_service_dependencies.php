<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_service_dependencies
{
    private application_infrastructure_runtime_dependencies $runtime;
    private application_infrastructure_storage_dependencies $storage;

    public function __construct(container_interface $container)
    {
        $this->runtime = new application_infrastructure_runtime_dependencies($container);
        $this->storage = new application_infrastructure_storage_dependencies($container);
    }

    public function errorFactory(): callable
    {
        return $this->runtime->errorFactory();
    }

    public function bootstrapRuntime(): object
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function config(): object
    {
        return $this->runtime->config();
    }

    public function cacheFactory(): callable
    {
        return $this->runtime->cacheFactory();
    }

    public function fileSystemStorage(): object
    {
        return $this->storage->fileSystemStorage();
    }
}
