<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_role_transfer_dependencies
{
    private application_navigation_tab_role_factory_role_transfer_dependencies $roleFactory;
    private application_navigation_tab_transfer_factory_role_transfer_dependencies $transferFactory;

    public function __construct(container_interface $container)
    {
        $this->roleFactory = new application_navigation_tab_role_factory_role_transfer_dependencies($container);
        $this->transferFactory = new application_navigation_tab_transfer_factory_role_transfer_dependencies($container);
    }

    public function roleFactory(): callable
    {
        return $this->roleFactory->roleFactory();
    }

    public function transferFactory(): callable
    {
        return $this->transferFactory->transferFactory();
    }
}
