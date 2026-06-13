<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_tab_service_context_dependencies
{
    private application_core_project_tab_instance_service_context_dependencies $tab;
    private application_core_project_tab_factory_service_context_dependencies $tabFactory;

    public function __construct(container_interface $container)
    {
        $this->tab = new application_core_project_tab_instance_service_context_dependencies($container);
        $this->tabFactory = new application_core_project_tab_factory_service_context_dependencies($container);
    }

    public function tab(): object
    {
        return $this->tab->tab();
    }

    public function tabFactory(): callable
    {
        return $this->tabFactory->tabFactory();
    }
}
