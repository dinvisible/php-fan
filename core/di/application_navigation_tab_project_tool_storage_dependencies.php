<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_project_tool_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function projectToolFileStorage(): mixed
    {
        return $this->container->get(service_id::PROJECT_TOOL_FILE_STORAGE);
    }
}
