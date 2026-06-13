<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_pear_http_session_loader_native_runtime_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function pearHttpSessionLoader(): mixed
    {
        return $this->container->get(service_id::PEAR_HTTP_SESSION_LOADER);
    }
}
