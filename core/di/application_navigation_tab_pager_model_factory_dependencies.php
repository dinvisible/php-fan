<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_pager_model_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function pagerFactory(): callable
    {
        return fn(mixed ...$arguments): mixed => $this->container->get(service_id::PAGER, ...$arguments);
    }
}
