<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_error_log_media_error_helper_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'errorLogWriter' => $this->container->get('error_log_writer'),
        ];
    }
}
