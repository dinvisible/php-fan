<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_curl_transport_dependencies
{
    private application_client_curl_adapter_transport_dependencies $curlAdapter;
    private application_client_curl_factory_transport_dependencies $curlFactory;

    public function __construct(container_interface $container)
    {
        $this->curlAdapter = new application_client_curl_adapter_transport_dependencies($container);
        $this->curlFactory = new application_client_curl_factory_transport_dependencies($container);
    }

    public function curlAdapter(): object
    {
        return $this->curlAdapter->curlAdapter();
    }

    public function curlFactory(): callable
    {
        return $this->curlFactory->curlFactory();
    }
}
