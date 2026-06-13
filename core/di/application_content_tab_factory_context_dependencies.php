<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_tab_factory_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function tabFactory(): callable
    {
        return fn(): mixed => $this->container->get(service_id::TAB);
    }
}
