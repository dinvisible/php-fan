<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_service_factory_config_header_dependencies
{
    private application_navigation_tab_config_factory_config_header_dependencies $configFactory;
    private application_navigation_tab_header_factory_config_header_dependencies $headerFactory;

    public function __construct(container_interface $container)
    {
        $this->configFactory = new application_navigation_tab_config_factory_config_header_dependencies($container);
        $this->headerFactory = new application_navigation_tab_header_factory_config_header_dependencies($container);
    }

    public function configFactory(): callable
    {
        return $this->configFactory->configFactory();
    }

    public function headerFactory(): callable
    {
        return $this->headerFactory->headerFactory();
    }
}
