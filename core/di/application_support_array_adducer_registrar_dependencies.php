<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_array_adducer_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayAdducer(): callable
    {
        return $this->container->get(service_id::ARRAY_ADDUCER);
    }
}
