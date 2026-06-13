<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_media_core_factory_group
{
    private base_dependency_obfuscator_media_core_factory_group $obfuscator;
    private base_dependency_image_modify_media_core_factory_group $imageModify;

    public function __construct(container_interface $container)
    {
        $this->obfuscator = new base_dependency_obfuscator_media_core_factory_group($container);
        $this->imageModify = new base_dependency_image_modify_media_core_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->obfuscator->dependencies(),
            $this->imageModify->dependencies()
        );
    }
}
