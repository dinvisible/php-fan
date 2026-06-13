<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_plain_route_dependencies
{
    private application_controller_matcher_plain_route_dependencies $matcher;
    private application_controller_header_plain_route_dependencies $header;

    public function __construct(container_interface $container)
    {
        $this->matcher = new application_controller_matcher_plain_route_dependencies($container);
        $this->header = new application_controller_header_plain_route_dependencies($container);
    }

    public function matcher(): object
    {
        return $this->matcher->matcher();
    }

    public function header(): object
    {
        return $this->header->header();
    }
}
