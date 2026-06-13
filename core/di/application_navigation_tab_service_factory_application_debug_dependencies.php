<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_application_debug_dependencies
{
    private application_navigation_tab_application_factory_application_debug_dependencies $applicationFactory;
    private application_navigation_tab_debug_factory_application_debug_dependencies $debugFactory;

    public function __construct(container_interface $container)
    {
        $this->applicationFactory = new application_navigation_tab_application_factory_application_debug_dependencies($container);
        $this->debugFactory = new application_navigation_tab_debug_factory_application_debug_dependencies($container);
    }

    public function applicationFactory(): callable
    {
        return $this->applicationFactory->applicationFactory();
    }

    public function debugFactory(): callable
    {
        return $this->debugFactory->debugFactory();
    }
}
