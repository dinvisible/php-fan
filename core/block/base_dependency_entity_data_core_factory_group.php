<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_entity_data_core_factory_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'entityFactory' => fn(mixed ...$arguments): mixed => $this->container->get('entity', ...$arguments),
        ];
    }
}
