<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_routing_context_dependencies
{
    private application_navigation_tab_matcher_routing_context_dependencies $matcher;
    private application_navigation_tab_request_routing_context_dependencies $request;

    public function __construct(container_interface $container)
    {
        $this->matcher = new application_navigation_tab_matcher_routing_context_dependencies($container);
        $this->request = new application_navigation_tab_request_routing_context_dependencies($container);
    }

    public function matcher(): mixed
    {
        return $this->matcher->matcher();
    }

    public function request(): mixed
    {
        return $this->request->request();
    }
}
