<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_request_context_group
{
    private base_dependency_request_role_context_group $requestRole;
    private base_dependency_session_context_group $session;

    public function __construct(container_interface $container)
    {
        $this->requestRole = new base_dependency_request_role_context_group($container);
        $this->session = new base_dependency_session_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->requestRole->dependencies(),
            $this->session->dependencies()
        );
    }
}
