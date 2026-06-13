<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_meta_row_factory_exception_meta_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function metaRowFactory(): mixed
    {
        return $this->container->get(service_id::META_ROW_FACTORY);
    }
}
