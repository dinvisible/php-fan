<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_service_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function pagerState(): object
    {
        return $this->container->get(service_id::PAGER_STATE);
    }
}
