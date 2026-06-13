<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_context_dependencies
{
    private application_session_application_context_dependencies $applicationContext;
    private application_session_request_context_dependencies $requestContext;
    private application_session_header_context_dependencies $headerContext;

    public function __construct(container_interface $container)
    {
        $this->applicationContext = new application_session_application_context_dependencies($container);
        $this->requestContext = new application_session_request_context_dependencies($container);
        $this->headerContext = new application_session_header_context_dependencies($container);
    }

    public function config(): mixed
    {
        return $this->applicationContext->config();
    }

    public function application(): mixed
    {
        return $this->applicationContext->application();
    }

    public function requestInput(): mixed
    {
        return $this->requestContext->requestInput();
    }

    public function headerWriter(): mixed
    {
        return $this->headerContext->headerWriter();
    }

    public function request(): mixed
    {
        return $this->requestContext->request();
    }
}
