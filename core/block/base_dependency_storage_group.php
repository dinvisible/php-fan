<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_storage_group
{
    private base_dependency_block_file_storage_group $blockFiles;
    private base_dependency_project_file_storage_group $projectFiles;
    private base_dependency_upload_limit_storage_group $uploadLimit;

    public function __construct(container_interface $container)
    {
        $this->blockFiles = new base_dependency_block_file_storage_group($container);
        $this->projectFiles = new base_dependency_project_file_storage_group($container);
        $this->uploadLimit = new base_dependency_upload_limit_storage_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->blockFiles->dependencies(),
            $this->projectFiles->dependencies(),
            $this->uploadLimit->dependencies()
        );
    }
}
