<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_modify_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageModifyState(): object
    {
        return $this->container->get(service_id::IMAGE_MODIFY_STATE);
    }
}
