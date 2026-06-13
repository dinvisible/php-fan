<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_plain_exception_factory_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function plainExceptionFactory(): callable
    {
        return $this->container->get(service_id::PLAIN_EXCEPTION_FACTORY);
    }
}
