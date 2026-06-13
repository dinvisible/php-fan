<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_array_like_checker_read_check_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function arrayLikeChecker(): mixed
    {
        return $this->container->get(service_id::ARRAY_LIKE_CHECKER);
    }
}
