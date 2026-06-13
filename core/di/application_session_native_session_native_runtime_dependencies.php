<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_native_session_native_runtime_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function nativeSession(): mixed
    {
        return $this->container->get(service_id::NATIVE_SESSION);
    }
}
