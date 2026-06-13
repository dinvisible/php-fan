<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_array_payload_dependencies
{
    private application_client_array_adducer_payload_dependencies $arrayAdducer;
    private application_client_array_value_reader_payload_dependencies $arrayValueReader;

    public function __construct(container_interface $container)
    {
        $this->arrayAdducer = new application_client_array_adducer_payload_dependencies($container);
        $this->arrayValueReader = new application_client_array_value_reader_payload_dependencies($container);
    }

    public function arrayAdducer(): callable
    {
        return $this->arrayAdducer->arrayAdducer();
    }

    public function arrayValueReader(): callable
    {
        return $this->arrayValueReader->arrayValueReader();
    }
}
