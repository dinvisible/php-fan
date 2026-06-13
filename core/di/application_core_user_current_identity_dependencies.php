<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_user_current_identity_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function currentUserFactory(): callable
    {
        return fn(bool $checkLogout): mixed => $this->container->get($checkLogout ? service_id::CURRENT_USER_CHECKED : service_id::CURRENT_USER);
    }
}
