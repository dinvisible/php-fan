<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_date_factory_user_time_model_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function dateFactory(): callable
    {
        return fn(mixed ...$arguments): mixed => $this->container->get(service_id::DATE, ...$arguments);
    }
}
