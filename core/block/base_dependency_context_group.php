<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_context_group
{
    private base_dependency_runtime_context_group $runtime;
    private base_dependency_request_context_group $request;
    private base_dependency_navigation_context_group $navigation;

    public function __construct(container_interface $container)
    {
        $this->runtime = new base_dependency_runtime_context_group($container);
        $this->request = new base_dependency_request_context_group($container);
        $this->navigation = new base_dependency_navigation_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->runtime->dependencies(),
            $this->request->dependencies(),
            $this->navigation->dependencies()
        );
    }
}
