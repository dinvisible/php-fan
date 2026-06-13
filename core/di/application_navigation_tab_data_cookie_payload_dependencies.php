<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_data_cookie_payload_dependencies
{
    private application_navigation_tab_data_loader_payload_dependencies $dataLoaderFactory;
    private application_navigation_tab_cookie_payload_dependencies $cookieFactory;

    public function __construct(container_interface $container)
    {
        $this->dataLoaderFactory = new application_navigation_tab_data_loader_payload_dependencies($container);
        $this->cookieFactory = new application_navigation_tab_cookie_payload_dependencies($container);
    }

    public function dataLoaderFactory(): callable
    {
        return $this->dataLoaderFactory->dataLoaderFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->cookieFactory->cookieFactory();
    }
}
