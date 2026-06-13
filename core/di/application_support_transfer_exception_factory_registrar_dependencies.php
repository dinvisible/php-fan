<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_transfer_exception_factory_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function transferExceptionFactory(): callable
    {
        return $this->container->get(service_id::TRANSFER_EXCEPTION_FACTORY);
    }
}
