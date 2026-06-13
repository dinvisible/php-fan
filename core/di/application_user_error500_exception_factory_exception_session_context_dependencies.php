<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_error500_exception_factory_exception_session_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->container->get(service_id::ERROR500_EXCEPTION_FACTORY);
    }
}
