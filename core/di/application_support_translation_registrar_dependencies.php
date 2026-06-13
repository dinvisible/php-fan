<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_translation_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function translation(): object
    {
        return $this->container->get(service_id::TRANSLATION);
    }
}
