<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_error_dependencies
{
    private application_core_project_error_service_dependencies $service;
    private application_core_project_error_storage_dependencies $storage;

    public function __construct(container_interface $container)
    {
        $this->service = new application_core_project_error_service_dependencies($container);
        $this->storage = new application_core_project_error_storage_dependencies($container);
    }

    public function error(): object
    {
        return $this->service->error();
    }

    public function errorFactory(): callable
    {
        return $this->service->errorFactory();
    }

    public function errorLogWriter(): object
    {
        return $this->storage->errorLogWriter();
    }

    public function errorFileStorage(): object
    {
        return $this->storage->errorFileStorage();
    }
}
