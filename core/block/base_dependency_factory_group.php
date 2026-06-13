<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_factory_group
{
    private base_dependency_data_factory_group $data;
    private base_dependency_application_factory_group $application;
    private base_dependency_media_factory_group $media;

    public function __construct(container_interface $container)
    {
        $this->data = new base_dependency_data_factory_group($container);
        $this->application = new base_dependency_application_factory_group($container);
        $this->media = new base_dependency_media_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->data->dependencies(),
            $this->application->dependencies(),
            $this->media->dependencies()
        );
    }
}
