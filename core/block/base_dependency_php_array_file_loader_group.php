<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_php_array_file_loader_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'phpArrayFileLoader' => $this->container->get('php_array_file_loader'),
        ];
    }
}
