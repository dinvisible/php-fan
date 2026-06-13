<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_tab_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function tab(): object
    {
        return $this->container->get(service_id::TAB);
    }

    public function tabFactory(): callable
    {
        return fn(): object => $this->tab();
    }
}
