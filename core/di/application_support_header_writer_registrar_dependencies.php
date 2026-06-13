<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_header_writer_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function headerWriter(): object
    {
        return $this->container->get(service_id::HEADER_WRITER);
    }
}
