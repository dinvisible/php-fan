<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_current_user_factory_identity_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function currentUserFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::CURRENT_USER);
    }
}
