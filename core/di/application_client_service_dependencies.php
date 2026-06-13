<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_service_dependencies
{
    private application_client_runtime_dependencies $runtime;
    private application_client_transport_dependencies $transport;
    private application_client_payload_dependencies $payload;

    public function __construct(container_interface $container)
    {
        $this->runtime = new application_client_runtime_dependencies($container);
        $this->transport = new application_client_transport_dependencies($container);
        $this->payload = new application_client_payload_dependencies($container);
    }

    public function bootstrapRuntime(): object
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function config(): object
    {
        return $this->runtime->config();
    }

    public function cacheFactory(): callable
    {
        return $this->runtime->cacheFactory();
    }

    public function curlAdapter(): object
    {
        return $this->transport->curlAdapter();
    }

    public function arrayAdducer(): callable
    {
        return $this->payload->arrayAdducer();
    }

    public function arrayValueReader(): callable
    {
        return $this->payload->arrayValueReader();
    }

    public function jsonFactory(): callable
    {
        return $this->transport->jsonFactory();
    }

    public function curlFactory(): callable
    {
        return $this->transport->curlFactory();
    }

    public function errorFactory(): callable
    {
        return $this->transport->errorFactory();
    }

    public function requestInput(): object
    {
        return $this->payload->requestInput();
    }

    public function serializerOperations(): object
    {
        return $this->payload->serializerOperations();
    }

    public function cookieWriter(): object
    {
        return $this->payload->cookieWriter();
    }
}
