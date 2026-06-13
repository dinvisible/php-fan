<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_image_metadata_media_error_helper_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'imageMetadataReader' => $this->container->get('image_metadata_reader'),
        ];
    }
}
