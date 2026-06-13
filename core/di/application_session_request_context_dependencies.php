<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_request_context_dependencies
{
    private application_session_request_input_request_context_dependencies $requestInput;
    private application_session_request_instance_request_context_dependencies $request;

    public function __construct(container_interface $container)
    {
        $this->requestInput = new application_session_request_input_request_context_dependencies($container);
        $this->request = new application_session_request_instance_request_context_dependencies($container);
    }

    public function requestInput(): mixed
    {
        return $this->requestInput->requestInput();
    }

    public function request(): mixed
    {
        return $this->request->request();
    }
}
