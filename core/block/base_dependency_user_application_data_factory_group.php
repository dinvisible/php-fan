<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_user_application_data_factory_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'userFactory' => fn(mixed ...$arguments): mixed => $this->container->get('user', ...$arguments),
        ];
    }
}
