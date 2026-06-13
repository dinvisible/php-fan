<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_source_file_storage_image_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageSourceFileStorage(): object
    {
        return $this->container->get(service_id::IMAGE_SOURCE_FILE_STORAGE);
    }
}
