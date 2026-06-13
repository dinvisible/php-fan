<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_runtime_error_dependencies
{
    private application_infrastructure_runtime_error_factory_dependencies $errorFactory;
    private application_infrastructure_runtime_bootstrap_runtime_error_dependencies $bootstrapRuntime;

    public function __construct(container_interface $container)
    {
        $this->errorFactory = new application_infrastructure_runtime_error_factory_dependencies($container);
        $this->bootstrapRuntime = new application_infrastructure_runtime_bootstrap_runtime_error_dependencies($container);
    }

    public function errorFactory(): callable
    {
        return $this->errorFactory->errorFactory();
    }

    public function bootstrapRuntime(): object
    {
        return $this->bootstrapRuntime->bootstrapRuntime();
    }
}
