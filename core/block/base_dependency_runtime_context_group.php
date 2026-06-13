<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_runtime_context_group
{
    private base_dependency_tab_runtime_context_group $tabRuntime;
    private base_dependency_request_input_context_group $requestInput;

    public function __construct(container_interface $container)
    {
        $this->tabRuntime = new base_dependency_tab_runtime_context_group($container);
        $this->requestInput = new base_dependency_request_input_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->tabRuntime->dependencies(),
            $this->requestInput->dependencies()
        );
    }
}
