<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_storage_dependencies
{
    private application_navigation_tab_file_storage_dependencies $fileStorage;
    private application_navigation_tab_project_tool_storage_dependencies $projectToolStorage;
    private application_navigation_tab_upload_limit_storage_dependencies $uploadLimit;

    public function __construct(container_interface $container)
    {
        $this->fileStorage = new application_navigation_tab_file_storage_dependencies($container);
        $this->projectToolStorage = new application_navigation_tab_project_tool_storage_dependencies($container);
        $this->uploadLimit = new application_navigation_tab_upload_limit_storage_dependencies($container);
    }

    public function blockFileStorage(): mixed
    {
        return $this->fileStorage->blockFileStorage();
    }

    public function metaFileStorage(): mixed
    {
        return $this->fileStorage->metaFileStorage();
    }

    public function projectToolFileStorage(): mixed
    {
        return $this->projectToolStorage->projectToolFileStorage();
    }

    public function rootHtmlFileStorage(): mixed
    {
        return $this->fileStorage->rootHtmlFileStorage();
    }

    public function uploadSizeLimitProvider(): mixed
    {
        return $this->uploadLimit->uploadSizeLimitProvider();
    }
}
