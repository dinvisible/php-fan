<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_recursive_merger_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function recursiveMerger(): callable
    {
        return $this->container->get(service_id::RECURSIVE_MERGER);
    }
}
