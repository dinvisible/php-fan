<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_user_current_user_space_factory_session_space_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function currentUserSpaceFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::CURRENT_USER_SPACE);
    }
}
