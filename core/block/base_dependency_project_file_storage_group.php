<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_project_file_storage_group
{
    private base_dependency_project_tool_file_storage_group $projectTool;
    private base_dependency_root_html_file_storage_group $rootHtml;

    public function __construct(container_interface $container)
    {
        $this->projectTool = new base_dependency_project_tool_file_storage_group($container);
        $this->rootHtml = new base_dependency_root_html_file_storage_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->projectTool->dependencies(),
            $this->rootHtml->dependencies()
        );
    }
}
