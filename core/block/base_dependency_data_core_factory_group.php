<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_data_core_factory_group
{
    private base_dependency_entity_data_core_factory_group $entity;
    private base_dependency_json_data_core_factory_group $json;

    public function __construct(container_interface $container)
    {
        $this->entity = new base_dependency_entity_data_core_factory_group($container);
        $this->json = new base_dependency_json_data_core_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->entity->dependencies(),
            $this->json->dependencies()
        );
    }
}
