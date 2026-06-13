<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_view_loader_text_keeper_factory_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function viewLoaderTextKeeperFactory(): callable
    {
        return $this->container->get(service_id::VIEW_LOADER_TEXT_KEEPER_FACTORY);
    }
}
