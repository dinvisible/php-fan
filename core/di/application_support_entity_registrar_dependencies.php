<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_entity_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function entity(): object
    {
        return $this->container->get(service_id::ENTITY);
    }
}
