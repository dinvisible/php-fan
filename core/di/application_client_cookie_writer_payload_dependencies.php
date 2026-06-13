<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_cookie_writer_payload_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function cookieWriter(): object
    {
        return $this->container->get(service_id::COOKIE_WRITER);
    }
}
