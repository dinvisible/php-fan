<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_curl_adapter_transport_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function curlAdapter(): object
    {
        return $this->container->get(service_id::CURL_ADAPTER);
    }
}
