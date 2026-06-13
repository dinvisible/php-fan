<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_dependencies
{
    private application_utility_image_storage_dependencies $storage;
    private application_utility_image_metadata_resource_dependencies $metadataResource;
    private application_utility_image_canvas_output_dependencies $canvasOutput;

    public function __construct(container_interface $container)
    {
        $this->storage = new application_utility_image_storage_dependencies($container);
        $this->metadataResource = new application_utility_image_metadata_resource_dependencies($container);
        $this->canvasOutput = new application_utility_image_canvas_output_dependencies($container);
    }

    public function obfuscatorFileStorage(): object
    {
        return $this->storage->obfuscatorFileStorage();
    }

    public function imageMetadataReader(): object
    {
        return $this->metadataResource->imageMetadataReader();
    }

    public function imageResourceFactory(): object
    {
        return $this->metadataResource->imageResourceFactory();
    }

    public function imageCanvasOperations(): object
    {
        return $this->canvasOutput->imageCanvasOperations();
    }

    public function imageOutputWriter(): object
    {
        return $this->canvasOutput->imageOutputWriter();
    }

    public function imageSourceFileStorage(): object
    {
        return $this->storage->imageSourceFileStorage();
    }
}
