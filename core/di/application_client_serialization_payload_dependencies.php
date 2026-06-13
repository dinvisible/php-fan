<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_serialization_payload_dependencies
{
    private application_client_serializer_operations_payload_dependencies $serializerOperations;
    private application_client_cookie_writer_payload_dependencies $cookieWriter;

    public function __construct(container_interface $container)
    {
        $this->serializerOperations = new application_client_serializer_operations_payload_dependencies($container);
        $this->cookieWriter = new application_client_cookie_writer_payload_dependencies($container);
    }

    public function serializerOperations(): object
    {
        return $this->serializerOperations->serializerOperations();
    }

    public function cookieWriter(): object
    {
        return $this->cookieWriter->cookieWriter();
    }
}
