<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_root_html_file_storage_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'rootHtmlFileStorage' => $this->container->has('root_html_file_storage') ? $this->container->get('root_html_file_storage') : null,
        ];
    }
}
