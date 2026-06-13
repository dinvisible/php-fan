<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_exception_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->container->get(service_id::ERROR500_EXCEPTION_FACTORY);
    }
}
