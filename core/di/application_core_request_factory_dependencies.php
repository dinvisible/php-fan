<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_factory_dependencies
{
    private application_core_request_input_factory_dependencies $input;
    private application_core_request_transport_factory_dependencies $transport;
    private application_core_request_runtime_factory_dependencies $runtime;

    public function __construct(container_interface $container)
    {
        $this->input = new application_core_request_input_factory_dependencies($container);
        $this->transport = new application_core_request_transport_factory_dependencies($container);
        $this->runtime = new application_core_request_runtime_factory_dependencies($container);
    }

    public function requestInput(): object
    {
        return $this->input->requestInput();
    }

    public function jsonFactory(): callable
    {
        return $this->transport->jsonFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->transport->cookieFactory();
    }

    public function matcherFactory(): callable
    {
        return $this->runtime->matcherFactory();
    }

    public function requestFactory(): callable
    {
        return $this->runtime->requestFactory();
    }
}
