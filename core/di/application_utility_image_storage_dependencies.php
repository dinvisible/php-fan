<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_storage_dependencies
{
    private application_utility_obfuscator_file_storage_image_storage_dependencies $obfuscatorFileStorage;
    private application_utility_image_source_file_storage_image_storage_dependencies $imageSourceFileStorage;

    public function __construct(container_interface $container)
    {
        $this->obfuscatorFileStorage = new application_utility_obfuscator_file_storage_image_storage_dependencies($container);
        $this->imageSourceFileStorage = new application_utility_image_source_file_storage_image_storage_dependencies($container);
    }

    public function obfuscatorFileStorage(): object
    {
        return $this->obfuscatorFileStorage->obfuscatorFileStorage();
    }

    public function imageSourceFileStorage(): object
    {
        return $this->imageSourceFileStorage->imageSourceFileStorage();
    }
}
