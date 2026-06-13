<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_image_modify_factory_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageModifyFactory(): callable
    {
        return fn(string $sourcePath): mixed => $this->container->get(service_id::IMAGE_MODIFY, $sourcePath);
    }
}
