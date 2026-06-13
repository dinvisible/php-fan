<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_exception_session_context_dependencies
{
    private application_user_error500_exception_factory_exception_session_context_dependencies $error500ExceptionFactory;
    private application_user_session_exception_session_context_dependencies $session;

    public function __construct(container_interface $container)
    {
        $this->error500ExceptionFactory = new application_user_error500_exception_factory_exception_session_context_dependencies($container);
        $this->session = new application_user_session_exception_session_context_dependencies($container);
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->error500ExceptionFactory->error500ExceptionFactory();
    }

    public function session(string $namespace, string $group): mixed
    {
        return $this->session->session($namespace, $group);
    }
}
