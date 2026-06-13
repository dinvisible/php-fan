<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_array_adducer_transform_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayAdducer(): mixed
    {
        return $this->container->get(service_id::ARRAY_ADDUCER);
    }
}
