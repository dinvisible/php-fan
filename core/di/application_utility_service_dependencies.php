<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_service_dependencies
{
    private application_utility_core_dependencies $core;
    private application_utility_image_dependencies $image;
    private application_utility_storage_dependencies $storage;

    public function __construct(container_interface $container)
    {
        $this->core = new application_utility_core_dependencies($container);
        $this->image = new application_utility_image_dependencies($container);
        $this->storage = new application_utility_storage_dependencies($container);
    }

    public function bootstrapRuntime(): object
    {
        return $this->core->bootstrapRuntime();
    }

    public function config(): object
    {
        return $this->core->config();
    }

    public function cacheFactory(): callable
    {
        return $this->core->cacheFactory();
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->storage->phpArrayFileLoader();
    }

    public function obfuscatorFileStorage(): object
    {
        return $this->image->obfuscatorFileStorage();
    }

    public function imageMetadataReader(): object
    {
        return $this->image->imageMetadataReader();
    }

    public function imageResourceFactory(): object
    {
        return $this->image->imageResourceFactory();
    }

    public function imageCanvasOperations(): object
    {
        return $this->image->imageCanvasOperations();
    }

    public function imageOutputWriter(): object
    {
        return $this->image->imageOutputWriter();
    }

    public function imageSourceFileStorage(): object
    {
        return $this->image->imageSourceFileStorage();
    }

    public function arrayValueReader(): callable
    {
        return $this->core->arrayValueReader();
    }

    public function errorFactory(): callable
    {
        return $this->core->errorFactory();
    }

    public function phpRuntimeSettings(): object
    {
        return $this->core->phpRuntimeSettings();
    }

    public function soapWsdlFileStorage(): object
    {
        return $this->storage->soapWsdlFileStorage();
    }

    public function classNameResolver(): callable
    {
        return $this->storage->classNameResolver();
    }
}
