<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_root_html_file_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function rootHtmlFileStorage(): mixed
    {
        return $this->container->get(service_id::ROOT_HTML_FILE_STORAGE);
    }
}
