<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_obfuscator_file_storage_image_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function obfuscatorFileStorage(): object
    {
        return $this->container->get(service_id::OBFUSCATOR_FILE_STORAGE);
    }
}
