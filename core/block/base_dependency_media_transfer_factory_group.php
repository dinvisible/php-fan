<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_media_transfer_factory_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'logFactory' => null,
            'transferFactory' => fn(mixed ...$arguments): mixed => $this->container->get('transfer', ...$arguments),
        ];
    }
}
