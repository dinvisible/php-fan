<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_helper_group
{
    private base_dependency_array_helper_group $array;
    private base_dependency_class_helper_group $class;
    private base_dependency_media_error_helper_group $mediaError;

    public function __construct(container_interface $container)
    {
        $this->array = new base_dependency_array_helper_group($container);
        $this->class = new base_dependency_class_helper_group($container);
        $this->mediaError = new base_dependency_media_error_helper_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->array->dependencies(),
            $this->class->dependencies(),
            $this->mediaError->dependencies()
        );
    }
}
