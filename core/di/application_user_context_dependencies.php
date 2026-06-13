<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_context_dependencies
{
    private application_user_serialization_config_context_dependencies $serializationConfig;
    private application_user_application_request_context_dependencies $applicationRequest;
    private application_user_exception_session_context_dependencies $exceptionSession;

    public function __construct(container_interface $container)
    {
        $this->serializationConfig = new application_user_serialization_config_context_dependencies($container);
        $this->applicationRequest = new application_user_application_request_context_dependencies($container);
        $this->exceptionSession = new application_user_exception_session_context_dependencies($container);
    }

    public function serializerOperations(): mixed
    {
        return $this->serializationConfig->serializerOperations();
    }

    public function config(): mixed
    {
        return $this->serializationConfig->config();
    }

    public function request(): mixed
    {
        return $this->applicationRequest->request();
    }

    public function application(): mixed
    {
        return $this->applicationRequest->application();
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->exceptionSession->error500ExceptionFactory();
    }

    public function session(string $namespace, string $group): mixed
    {
        return $this->exceptionSession->session($namespace, $group);
    }
}
