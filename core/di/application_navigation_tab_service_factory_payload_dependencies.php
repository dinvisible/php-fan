<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_payload_dependencies
{
    private application_navigation_tab_json_payload_dependencies $json;
    private application_navigation_tab_data_cookie_payload_dependencies $dataCookie;

    public function __construct(container_interface $container)
    {
        $this->json = new application_navigation_tab_json_payload_dependencies($container);
        $this->dataCookie = new application_navigation_tab_data_cookie_payload_dependencies($container);
    }

    public function jsonFactory(): callable
    {
        return $this->json->jsonFactory();
    }

    public function dataLoaderFactory(): callable
    {
        return $this->dataCookie->dataLoaderFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->dataCookie->cookieFactory();
    }
}
