<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_storage_dependencies
{
    private application_core_project_reflection_meta_dependencies $reflectionMeta;
    private application_core_project_response_loader_dependencies $responseLoader;

    public function __construct(container_interface $container)
    {
        $this->reflectionMeta = new application_core_project_reflection_meta_dependencies($container);
        $this->responseLoader = new application_core_project_response_loader_dependencies($container);
    }

    public function reflectionClassFactory(): object
    {
        return $this->reflectionMeta->reflectionClassFactory();
    }

    public function metaFileStorage(): object
    {
        return $this->reflectionMeta->metaFileStorage();
    }

    public function headerWriter(): object
    {
        return $this->responseLoader->headerWriter();
    }

    public function phpArrayFileLoader(): object
    {
        return $this->responseLoader->phpArrayFileLoader();
    }
}
