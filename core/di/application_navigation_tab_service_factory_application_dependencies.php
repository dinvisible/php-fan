<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_application_dependencies
{
    private application_navigation_tab_service_factory_role_transfer_dependencies $roleTransfer;
    private application_navigation_tab_service_factory_application_debug_dependencies $applicationDebug;

    public function __construct(container_interface $container)
    {
        $this->roleTransfer = new application_navigation_tab_service_factory_role_transfer_dependencies($container);
        $this->applicationDebug = new application_navigation_tab_service_factory_application_debug_dependencies($container);
    }

    public function roleFactory(): callable
    {
        return $this->roleTransfer->roleFactory();
    }

    public function transferFactory(): callable
    {
        return $this->roleTransfer->transferFactory();
    }

    public function applicationFactory(): callable
    {
        return $this->applicationDebug->applicationFactory();
    }

    public function debugFactory(): callable
    {
        return $this->applicationDebug->debugFactory();
    }
}
