<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_project_tool_file_storage_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'projectToolFileStorage' => $this->container->has('project_tool_file_storage') ? $this->container->get('project_tool_file_storage') : null,
        ];
    }
}
