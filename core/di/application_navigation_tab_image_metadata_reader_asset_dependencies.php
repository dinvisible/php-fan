<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_image_metadata_reader_asset_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageMetadataReader(): mixed
    {
        return $this->container->get(service_id::IMAGE_METADATA_READER);
    }
}
