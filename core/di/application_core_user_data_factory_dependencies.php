<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_user_data_factory_dependencies
{
    private application_core_user_date_data_factory_dependencies $dateFactory;
    private application_core_user_entity_data_factory_dependencies $entityFactory;

    public function __construct(container_interface $container)
    {
        $this->dateFactory = new application_core_user_date_data_factory_dependencies($container);
        $this->entityFactory = new application_core_user_entity_data_factory_dependencies($container);
    }

    public function dateFactory(): callable
    {
        return $this->dateFactory->dateFactory();
    }

    public function entityFactory(): callable
    {
        return $this->entityFactory->entityFactory();
    }
}
