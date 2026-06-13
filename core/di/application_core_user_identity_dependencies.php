<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_user_identity_dependencies
{
    private application_core_user_current_identity_dependencies $currentUser;
    private application_core_user_session_space_dependencies $sessionSpace;

    public function __construct(container_interface $container)
    {
        $this->currentUser = new application_core_user_current_identity_dependencies($container);
        $this->sessionSpace = new application_core_user_session_space_dependencies($container);
    }

    public function currentUserFactory(): callable
    {
        return $this->currentUser->currentUserFactory();
    }

    public function sessionFactory(): callable
    {
        return $this->sessionSpace->sessionFactory();
    }

    public function currentUserSpaceFactory(): callable
    {
        return $this->sessionSpace->currentUserSpaceFactory();
    }
}
