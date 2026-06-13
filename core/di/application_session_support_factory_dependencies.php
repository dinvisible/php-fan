<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_support_factory_dependencies
{
    private application_session_error_factory_support_factory_dependencies $errorFactory;
    private application_session_date_factory_support_factory_dependencies $dateFactory;

    public function __construct(container_interface $container)
    {
        $this->errorFactory = new application_session_error_factory_support_factory_dependencies($container);
        $this->dateFactory = new application_session_date_factory_support_factory_dependencies($container);
    }

    public function errorFactory(): callable
    {
        return $this->errorFactory->errorFactory();
    }

    public function dateFactory(): callable
    {
        return $this->dateFactory->dateFactory();
    }
}
