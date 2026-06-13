<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_media_factory_group
{
    private base_dependency_media_core_factory_group $media;
    private base_dependency_media_transfer_factory_group $transfer;

    public function __construct(container_interface $container)
    {
        $this->media = new base_dependency_media_core_factory_group($container);
        $this->transfer = new base_dependency_media_transfer_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->media->dependencies(),
            $this->transfer->dependencies()
        );
    }
}
