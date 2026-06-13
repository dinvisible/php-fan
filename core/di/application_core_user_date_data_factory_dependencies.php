<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_user_date_data_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function dateFactory(): callable
    {
        return fn(?string $date = null, mixed $format = null): mixed => $this->container->get(service_id::DATE, $date, $format);
    }
}
