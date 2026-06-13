<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_identity_factory_dependencies
{
    private application_user_session_factory_identity_factory_dependencies $sessionFactory;
    private application_user_current_user_factory_identity_factory_dependencies $currentUserFactory;

    public function __construct(container_interface $container)
    {
        $this->sessionFactory = new application_user_session_factory_identity_factory_dependencies($container);
        $this->currentUserFactory = new application_user_current_user_factory_identity_factory_dependencies($container);
    }

    public function sessionFactory(): callable
    {
        return $this->sessionFactory->sessionFactory();
    }

    public function currentUserFactory(): callable
    {
        return $this->currentUserFactory->currentUserFactory();
    }
}
