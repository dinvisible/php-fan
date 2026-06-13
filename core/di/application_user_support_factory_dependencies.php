<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_support_factory_dependencies
{
    private application_user_error_factory_support_factory_dependencies $errorFactory;
    private application_user_entity_factory_support_factory_dependencies $entityFactory;

    public function __construct(container_interface $container)
    {
        $this->errorFactory = new application_user_error_factory_support_factory_dependencies($container);
        $this->entityFactory = new application_user_entity_factory_support_factory_dependencies($container);
    }

    public function errorFactory(): callable
    {
        return $this->errorFactory->errorFactory();
    }

    public function entityFactory(): callable
    {
        return $this->entityFactory->entityFactory();
    }
}
