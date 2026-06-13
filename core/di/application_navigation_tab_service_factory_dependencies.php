<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_dependencies
{
    private application_navigation_tab_service_factory_application_dependencies $application;
    private application_navigation_tab_service_factory_runtime_dependencies $runtime;
    private application_navigation_tab_service_factory_payload_dependencies $payload;

    public function __construct(container_interface $container)
    {
        $this->application = new application_navigation_tab_service_factory_application_dependencies($container);
        $this->runtime = new application_navigation_tab_service_factory_runtime_dependencies($container);
        $this->payload = new application_navigation_tab_service_factory_payload_dependencies($container);
    }

    public function roleFactory(): callable
    {
        return $this->application->roleFactory();
    }

    public function transferFactory(): callable
    {
        return $this->application->transferFactory();
    }

    public function configFactory(): callable
    {
        return $this->runtime->configFactory();
    }

    public function headerFactory(): callable
    {
        return $this->runtime->headerFactory();
    }

    public function applicationFactory(): callable
    {
        return $this->application->applicationFactory();
    }

    public function debugFactory(): callable
    {
        return $this->application->debugFactory();
    }

    public function jsonFactory(): callable
    {
        return $this->payload->jsonFactory();
    }

    public function dataLoaderFactory(): callable
    {
        return $this->payload->dataLoaderFactory();
    }

    public function errorFactory(): callable
    {
        return $this->runtime->errorFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->payload->cookieFactory();
    }

    public function reflectorFactory(): callable
    {
        return $this->runtime->reflectorFactory();
    }
}
