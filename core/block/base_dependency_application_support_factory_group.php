<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_application_support_factory_group
{
    private base_dependency_error_application_support_factory_group $error;
    private base_dependency_date_application_support_factory_group $date;

    public function __construct(container_interface $container)
    {
        $this->error = new base_dependency_error_application_support_factory_group($container);
        $this->date = new base_dependency_date_application_support_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->error->dependencies(),
            $this->date->dependencies()
        );
    }
}
