<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_header_writer_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function headerWriter(): mixed
    {
        return $this->container->get(service_id::HEADER_WRITER);
    }
}
