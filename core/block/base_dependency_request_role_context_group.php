<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_request_role_context_group
{
    private base_dependency_request_factory_context_group $request;
    private base_dependency_role_factory_context_group $role;

    public function __construct(container_interface $container)
    {
        $this->request = new base_dependency_request_factory_context_group($container);
        $this->role = new base_dependency_role_factory_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->request->dependencies(),
            $this->role->dependencies()
        );
    }
}
