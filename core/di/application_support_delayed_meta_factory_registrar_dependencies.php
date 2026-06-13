<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_delayed_meta_factory_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function delayedMetaFactory(): callable
    {
        return $this->container->get(service_id::DELAYED_META_FACTORY);
    }
}
