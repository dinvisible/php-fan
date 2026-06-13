<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_error_service_dependencies
{
    private application_core_project_error_context_dependencies $error;
    private application_core_project_error_factory_service_dependencies $errorFactory;

    public function __construct(container_interface $container)
    {
        $this->error = new application_core_project_error_context_dependencies($container);
        $this->errorFactory = new application_core_project_error_factory_service_dependencies($container);
    }

    public function error(): object
    {
        return $this->error->error();
    }

    public function errorFactory(): callable
    {
        return $this->errorFactory->errorFactory();
    }
}
