<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_upload_limit_storage_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'uploadSizeLimitProvider' => $this->container->has('upload_size_limit_provider') ? $this->container->get('upload_size_limit_provider') : null,
        ];
    }
}
