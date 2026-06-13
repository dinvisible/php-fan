<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_cookie_transport_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function cookieFactory(): callable
    {
        return fn(mixed $path = null, mixed $domain = null): mixed => $this->container->get(service_id::COOKIE, $path, $domain);
    }
}
