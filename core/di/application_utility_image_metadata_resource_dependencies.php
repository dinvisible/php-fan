<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_metadata_resource_dependencies
{
    private application_utility_image_metadata_reader_metadata_resource_dependencies $imageMetadataReader;
    private application_utility_image_resource_factory_metadata_resource_dependencies $imageResourceFactory;

    public function __construct(container_interface $container)
    {
        $this->imageMetadataReader = new application_utility_image_metadata_reader_metadata_resource_dependencies($container);
        $this->imageResourceFactory = new application_utility_image_resource_factory_metadata_resource_dependencies($container);
    }

    public function imageMetadataReader(): object
    {
        return $this->imageMetadataReader->imageMetadataReader();
    }

    public function imageResourceFactory(): object
    {
        return $this->imageResourceFactory->imageResourceFactory();
    }
}
