<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_transport_factory_dependencies
{
    private application_core_request_json_transport_factory_dependencies $jsonFactory;
    private application_core_request_cookie_transport_factory_dependencies $cookieFactory;

    public function __construct(container_interface $container)
    {
        $this->jsonFactory = new application_core_request_json_transport_factory_dependencies($container);
        $this->cookieFactory = new application_core_request_cookie_transport_factory_dependencies($container);
    }

    public function jsonFactory(): callable
    {
        return $this->jsonFactory->jsonFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->cookieFactory->cookieFactory();
    }
}
