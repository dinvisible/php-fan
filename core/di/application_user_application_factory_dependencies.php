<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_application_factory_dependencies
{
    private application_user_config_factory_dependencies $config;
    private application_user_application_request_input_factory_dependencies $applicationRequestInput;

    public function __construct(container_interface $container)
    {
        $this->config = new application_user_config_factory_dependencies($container);
        $this->applicationRequestInput = new application_user_application_request_input_factory_dependencies($container);
    }

    public function configFactory(): callable
    {
        return $this->config->configFactory();
    }

    public function applicationFactory(): callable
    {
        return $this->applicationRequestInput->applicationFactory();
    }

    public function requestInputFactory(): callable
    {
        return $this->applicationRequestInput->requestInputFactory();
    }
}
