<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_reflection_meta_dependencies
{
    private application_core_project_reflection_class_factory_meta_dependencies $reflectionClassFactory;
    private application_core_project_meta_file_storage_dependencies $metaFileStorage;

    public function __construct(container_interface $container)
    {
        $this->reflectionClassFactory = new application_core_project_reflection_class_factory_meta_dependencies($container);
        $this->metaFileStorage = new application_core_project_meta_file_storage_dependencies($container);
    }

    public function reflectionClassFactory(): object
    {
        return $this->reflectionClassFactory->reflectionClassFactory();
    }

    public function metaFileStorage(): object
    {
        return $this->metaFileStorage->metaFileStorage();
    }
}
