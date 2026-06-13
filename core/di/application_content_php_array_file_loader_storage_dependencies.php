<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_php_array_file_loader_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function phpArrayFileLoader(): object
    {
        return $this->container->get(service_id::PHP_ARRAY_FILE_LOADER);
    }
}
