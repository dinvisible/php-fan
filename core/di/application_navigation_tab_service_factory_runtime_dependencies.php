<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_runtime_dependencies
{
    private application_navigation_tab_service_factory_config_header_dependencies $configHeader;
    private application_navigation_tab_service_factory_error_reflector_dependencies $errorReflector;

    public function __construct(container_interface $container)
    {
        $this->configHeader = new application_navigation_tab_service_factory_config_header_dependencies($container);
        $this->errorReflector = new application_navigation_tab_service_factory_error_reflector_dependencies($container);
    }

    public function configFactory(): callable
    {
        return $this->configHeader->configFactory();
    }

    public function headerFactory(): callable
    {
        return $this->configHeader->headerFactory();
    }

    public function errorFactory(): callable
    {
        return $this->errorReflector->errorFactory();
    }

    public function reflectorFactory(): callable
    {
        return $this->errorReflector->reflectorFactory();
    }
}
