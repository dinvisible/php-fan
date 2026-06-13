<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_file_storage_dependencies
{
    private application_navigation_tab_block_meta_file_storage_dependencies $blockMeta;
    private application_navigation_tab_root_html_file_storage_dependencies $rootHtml;

    public function __construct(container_interface $container)
    {
        $this->blockMeta = new application_navigation_tab_block_meta_file_storage_dependencies($container);
        $this->rootHtml = new application_navigation_tab_root_html_file_storage_dependencies($container);
    }

    public function blockFileStorage(): mixed
    {
        return $this->blockMeta->blockFileStorage();
    }

    public function metaFileStorage(): mixed
    {
        return $this->blockMeta->metaFileStorage();
    }

    public function rootHtmlFileStorage(): mixed
    {
        return $this->rootHtml->rootHtmlFileStorage();
    }
}
