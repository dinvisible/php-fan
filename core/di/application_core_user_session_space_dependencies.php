<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_user_session_space_dependencies
{
    private application_core_user_session_factory_session_space_dependencies $sessionFactory;
    private application_core_user_current_user_space_factory_session_space_dependencies $currentUserSpaceFactory;

    public function __construct(container_interface $container)
    {
        $this->sessionFactory = new application_core_user_session_factory_session_space_dependencies($container);
        $this->currentUserSpaceFactory = new application_core_user_current_user_space_factory_session_space_dependencies($container);
    }

    public function sessionFactory(): callable
    {
        return $this->sessionFactory->sessionFactory();
    }

    public function currentUserSpaceFactory(): callable
    {
        return $this->currentUserSpaceFactory->currentUserSpaceFactory();
    }
}
