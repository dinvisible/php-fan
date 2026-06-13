<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_tab_dependencies
{
    private application_core_project_tab_context_dependencies $tabContext;
    private application_core_project_application_context_dependencies $applicationContext;
    private application_core_project_route_storage_dependencies $routeStorage;

    public function __construct(container_interface $container)
    {
        $this->tabContext = new application_core_project_tab_context_dependencies($container);
        $this->applicationContext = new application_core_project_application_context_dependencies($container);
        $this->routeStorage = new application_core_project_route_storage_dependencies($container);
    }

    public function tab(): object
    {
        return $this->tabContext->tab();
    }

    public function tabFactory(): callable
    {
        return $this->tabContext->tabFactory();
    }

    public function locale(): object
    {
        return $this->tabContext->locale();
    }

    public function application(): object
    {
        return $this->applicationContext->application();
    }

    public function matcherRouteFileStorage(): object
    {
        return $this->routeStorage->matcherRouteFileStorage();
    }
}
