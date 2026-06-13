<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_reflection_class_factory_meta_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function reflectionClassFactory(): object
    {
        return $this->container->get(service_id::REFLECTION_CLASS_FACTORY);
    }
}
