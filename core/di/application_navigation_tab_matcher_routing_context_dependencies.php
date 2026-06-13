<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_matcher_routing_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function matcher(): mixed
    {
        return $this->container->get(service_id::MATCHER);
    }
}
