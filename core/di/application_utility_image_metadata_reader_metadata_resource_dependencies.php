<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_metadata_reader_metadata_resource_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageMetadataReader(): object
    {
        return $this->container->get(service_id::IMAGE_METADATA_READER);
    }
}
