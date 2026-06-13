<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_block_meta_file_storage_dependencies
{
    private application_navigation_tab_block_file_storage_dependencies $blockFileStorage;
    private application_navigation_tab_meta_file_storage_dependencies $metaFileStorage;

    public function __construct(container_interface $container)
    {
        $this->blockFileStorage = new application_navigation_tab_block_file_storage_dependencies($container);
        $this->metaFileStorage = new application_navigation_tab_meta_file_storage_dependencies($container);
    }

    public function blockFileStorage(): mixed
    {
        return $this->blockFileStorage->blockFileStorage();
    }

    public function metaFileStorage(): mixed
    {
        return $this->metaFileStorage->metaFileStorage();
    }
}
