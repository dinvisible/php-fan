<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_translation_file_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function translationFileStorage(): object
    {
        return $this->container->get(service_id::TRANSLATION_FILE_STORAGE);
    }
}
