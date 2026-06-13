<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_tab_runtime_context_group
{
    private base_dependency_tab_service_runtime_context_group $tab;
    private base_dependency_bootstrap_runtime_context_group $runtime;

    public function __construct(container_interface $container)
    {
        $this->tab = new base_dependency_tab_service_runtime_context_group($container);
        $this->runtime = new base_dependency_bootstrap_runtime_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->tab->dependencies(),
            $this->runtime->dependencies()
        );
    }
}
