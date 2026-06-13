<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_application_request_input_factory_dependencies
{
    private application_user_application_factory_application_request_input_factory_dependencies $applicationFactory;
    private application_user_request_input_factory_application_request_input_factory_dependencies $requestInputFactory;

    public function __construct(container_interface $container)
    {
        $this->applicationFactory = new application_user_application_factory_application_request_input_factory_dependencies($container);
        $this->requestInputFactory = new application_user_request_input_factory_application_request_input_factory_dependencies($container);
    }

    public function applicationFactory(): callable
    {
        return $this->applicationFactory->applicationFactory();
    }

    public function requestInputFactory(): callable
    {
        return $this->requestInputFactory->requestInputFactory();
    }
}
