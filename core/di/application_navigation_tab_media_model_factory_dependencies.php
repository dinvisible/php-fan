<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_media_model_factory_dependencies
{
    private application_navigation_tab_obfuscator_model_factory_dependencies $obfuscatorFactory;
    private application_navigation_tab_image_modify_model_factory_dependencies $imageModifyFactory;

    public function __construct(container_interface $container)
    {
        $this->obfuscatorFactory = new application_navigation_tab_obfuscator_model_factory_dependencies($container);
        $this->imageModifyFactory = new application_navigation_tab_image_modify_model_factory_dependencies($container);
    }

    public function obfuscatorFactory(): callable
    {
        return $this->obfuscatorFactory->obfuscatorFactory();
    }

    public function imageModifyFactory(): callable
    {
        return $this->imageModifyFactory->imageModifyFactory();
    }
}
