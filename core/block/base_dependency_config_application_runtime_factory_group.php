<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_config_application_runtime_factory_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'configFactory' => fn(mixed ...$arguments): mixed => $this->container->get('config', ...$arguments),
        ];
    }
}
