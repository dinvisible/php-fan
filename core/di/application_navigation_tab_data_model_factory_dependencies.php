<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_data_model_factory_dependencies
{
    private application_navigation_tab_entity_model_factory_dependencies $entityFactory;
    private application_navigation_tab_pager_model_factory_dependencies $pagerFactory;
    private application_navigation_tab_database_model_factory_dependencies $databaseFactory;

    public function __construct(container_interface $container)
    {
        $this->entityFactory = new application_navigation_tab_entity_model_factory_dependencies($container);
        $this->pagerFactory = new application_navigation_tab_pager_model_factory_dependencies($container);
        $this->databaseFactory = new application_navigation_tab_database_model_factory_dependencies($container);
    }

    public function entityFactory(): callable
    {
        return $this->entityFactory->entityFactory();
    }

    public function pagerFactory(): callable
    {
        return $this->pagerFactory->pagerFactory();
    }

    public function databaseFactory(): callable
    {
        return $this->databaseFactory->databaseFactory();
    }
}
