<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_application_data_factory_group
{
    private base_dependency_database_application_data_factory_group $database;
    private base_dependency_user_application_data_factory_group $user;

    public function __construct(container_interface $container)
    {
        $this->database = new base_dependency_database_application_data_factory_group($container);
        $this->user = new base_dependency_user_application_data_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->database->dependencies(),
            $this->user->dependencies()
        );
    }
}
