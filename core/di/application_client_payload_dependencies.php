<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_payload_dependencies
{
    private application_client_array_payload_dependencies $array;
    private application_client_request_payload_dependencies $request;
    private application_client_serialization_payload_dependencies $serialization;

    public function __construct(container_interface $container)
    {
        $this->array = new application_client_array_payload_dependencies($container);
        $this->request = new application_client_request_payload_dependencies($container);
        $this->serialization = new application_client_serialization_payload_dependencies($container);
    }

    public function arrayAdducer(): callable
    {
        return $this->array->arrayAdducer();
    }

    public function arrayValueReader(): callable
    {
        return $this->array->arrayValueReader();
    }

    public function requestInput(): object
    {
        return $this->request->requestInput();
    }

    public function serializerOperations(): object
    {
        return $this->serialization->serializerOperations();
    }

    public function cookieWriter(): object
    {
        return $this->serialization->cookieWriter();
    }
}
