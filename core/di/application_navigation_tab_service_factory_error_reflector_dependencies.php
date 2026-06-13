<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_error_reflector_dependencies
{
    private application_navigation_tab_error_factory_error_reflector_dependencies $errorFactory;
    private application_navigation_tab_reflector_factory_error_reflector_dependencies $reflectorFactory;

    public function __construct(container_interface $container)
    {
        $this->errorFactory = new application_navigation_tab_error_factory_error_reflector_dependencies($container);
        $this->reflectorFactory = new application_navigation_tab_reflector_factory_error_reflector_dependencies($container);
    }

    public function errorFactory(): callable
    {
        return $this->errorFactory->errorFactory();
    }

    public function reflectorFactory(): callable
    {
        return $this->reflectorFactory->reflectorFactory();
    }
}
