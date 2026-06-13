<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_meta_maker_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function metaMakerState(): object
    {
        return $this->container->get(service_id::META_MAKER_STATE);
    }
}
