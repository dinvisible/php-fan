<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_image_metadata_reader_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageMetadataReader(): object
    {
        return $this->container->get(service_id::IMAGE_METADATA_READER);
    }
}
