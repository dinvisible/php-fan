<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_transport_dependencies
{
    private application_client_curl_transport_dependencies $curl;
    private application_client_serialization_transport_dependencies $serialization;
    private application_client_error_transport_dependencies $error;

    public function __construct(container_interface $container)
    {
        $this->curl = new application_client_curl_transport_dependencies($container);
        $this->serialization = new application_client_serialization_transport_dependencies($container);
        $this->error = new application_client_error_transport_dependencies($container);
    }

    public function curlAdapter(): object
    {
        return $this->curl->curlAdapter();
    }

    public function jsonFactory(): callable
    {
        return $this->serialization->jsonFactory();
    }

    public function curlFactory(): callable
    {
        return $this->curl->curlFactory();
    }

    public function errorFactory(): callable
    {
        return $this->error->errorFactory();
    }
}
