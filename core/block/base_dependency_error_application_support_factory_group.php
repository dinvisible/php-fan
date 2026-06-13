<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_error_application_support_factory_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'errorFactory' => fn(): mixed => $this->container->get('error'),
        ];
    }
}
