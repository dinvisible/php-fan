<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_application_context_dependencies
{
    private application_session_config_application_context_dependencies $config;
    private application_session_application_instance_application_context_dependencies $application;

    public function __construct(container_interface $container)
    {
        $this->config = new application_session_config_application_context_dependencies($container);
        $this->application = new application_session_application_instance_application_context_dependencies($container);
    }

    public function config(): mixed
    {
        return $this->config->config();
    }

    public function application(): mixed
    {
        return $this->application->application();
    }
}
