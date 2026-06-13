<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_storage_dependencies
{
    private application_content_php_array_file_loader_storage_dependencies $phpArrayFileLoader;
    private application_content_translation_file_storage_dependencies $translationFileStorage;

    public function __construct(container_interface $container)
    {
        $this->phpArrayFileLoader = new application_content_php_array_file_loader_storage_dependencies($container);
        $this->translationFileStorage = new application_content_translation_file_storage_dependencies($container);
    }

    public function phpArrayFileLoader(): object
    {
        return $this->phpArrayFileLoader->phpArrayFileLoader();
    }

    public function translationFileStorage(): object
    {
        return $this->translationFileStorage->translationFileStorage();
    }
}
