<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_state_factory_dependencies
{
    private application_session_session_factory_state_factory_dependencies $sessionFactory;
    private application_session_cookie_factory_state_factory_dependencies $cookieFactory;

    public function __construct(container_interface $container)
    {
        $this->sessionFactory = new application_session_session_factory_state_factory_dependencies($container);
        $this->cookieFactory = new application_session_cookie_factory_state_factory_dependencies($container);
    }

    public function sessionFactory(): callable
    {
        return $this->sessionFactory->sessionFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->cookieFactory->cookieFactory();
    }
}
