<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_user_time_model_factory_dependencies
{
    private application_navigation_tab_user_factory_user_time_model_factory_dependencies $userFactory;
    private application_navigation_tab_date_factory_user_time_model_factory_dependencies $dateFactory;

    public function __construct(container_interface $container)
    {
        $this->userFactory = new application_navigation_tab_user_factory_user_time_model_factory_dependencies($container);
        $this->dateFactory = new application_navigation_tab_date_factory_user_time_model_factory_dependencies($container);
    }

    public function userFactory(): callable
    {
        return $this->userFactory->userFactory();
    }

    public function dateFactory(): callable
    {
        return $this->dateFactory->dateFactory();
    }
}
