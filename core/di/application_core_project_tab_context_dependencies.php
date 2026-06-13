<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_tab_context_dependencies
{
    private application_core_project_tab_service_context_dependencies $tabService;
    private application_core_project_locale_context_dependencies $localeContext;

    public function __construct(container_interface $container)
    {
        $this->tabService = new application_core_project_tab_service_context_dependencies($container);
        $this->localeContext = new application_core_project_locale_context_dependencies($container);
    }

    public function tab(): object
    {
        return $this->tabService->tab();
    }

    public function tabFactory(): callable
    {
        return $this->tabService->tabFactory();
    }

    public function locale(): object
    {
        return $this->localeContext->locale();
    }
}
