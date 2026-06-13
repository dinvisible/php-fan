<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_user_session_dependencies
{
    private application_core_user_identity_dependencies $identity;
    private application_core_user_data_factory_dependencies $data;

    public function __construct(container_interface $container)
    {
        $this->identity = new application_core_user_identity_dependencies($container);
        $this->data = new application_core_user_data_factory_dependencies($container);
    }

    public function currentUserFactory(): callable
    {
        return $this->identity->currentUserFactory();
    }

    public function sessionFactory(): callable
    {
        return $this->identity->sessionFactory();
    }

    public function currentUserSpaceFactory(): callable
    {
        return $this->identity->currentUserSpaceFactory();
    }

    public function dateFactory(): callable
    {
        return $this->data->dateFactory();
    }

    public function entityFactory(): callable
    {
        return $this->data->entityFactory();
    }
}
