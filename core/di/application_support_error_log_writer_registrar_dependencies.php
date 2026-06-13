<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_error_log_writer_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function errorLogWriter(): object
    {
        return $this->container->get(service_id::ERROR_LOG_WRITER);
    }
}
