<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_header_writer_response_loader_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function headerWriter(): object
    {
        return $this->container->get(service_id::HEADER_WRITER);
    }
}
