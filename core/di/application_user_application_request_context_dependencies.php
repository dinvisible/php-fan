<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_application_request_context_dependencies
{
    private application_user_request_application_request_context_dependencies $request;
    private application_user_application_instance_application_request_context_dependencies $application;

    public function __construct(container_interface $container)
    {
        $this->request = new application_user_request_application_request_context_dependencies($container);
        $this->application = new application_user_application_instance_application_request_context_dependencies($container);
    }

    public function request(): mixed
    {
        return $this->request->request();
    }

    public function application(): mixed
    {
        return $this->application->application();
    }
}
