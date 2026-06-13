<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_plain_config_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function plainConfigFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::CONFIG, 'plain');
    }
}
