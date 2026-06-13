<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_tab_resolver_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function tabResolver(): callable
    {
        return fn(): mixed => $this->container->get(service_id::TAB);
    }
}
