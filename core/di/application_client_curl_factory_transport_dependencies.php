<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_curl_factory_transport_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function curlFactory(): callable
    {
        return fn(string $url): mixed => $this->container->get(service_id::CURL, $url);
    }
}
