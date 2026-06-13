<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_error_log_writer_asset_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function errorLogWriter(): mixed
    {
        return $this->container->get(service_id::ERROR_LOG_WRITER);
    }
}
