<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_storage_dependencies
{
    private application_utility_file_storage_dependencies $fileStorage;
    private application_utility_class_storage_dependencies $classStorage;

    public function __construct(container_interface $container)
    {
        $this->fileStorage = new application_utility_file_storage_dependencies($container);
        $this->classStorage = new application_utility_class_storage_dependencies($container);
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->fileStorage->phpArrayFileLoader();
    }

    public function soapWsdlFileStorage(): object
    {
        return $this->fileStorage->soapWsdlFileStorage();
    }

    public function classNameResolver(): callable
    {
        return $this->classStorage->classNameResolver();
    }
}
