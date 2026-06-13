<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_factory_dependencies
{
    private application_session_support_factory_dependencies $support;
    private application_session_state_factory_dependencies $state;
    private application_session_cache_factory_dependencies $cache;

    public function __construct(container_interface $container)
    {
        $this->support = new application_session_support_factory_dependencies($container);
        $this->state = new application_session_state_factory_dependencies($container);
        $this->cache = new application_session_cache_factory_dependencies($container);
    }

    public function errorFactory(): callable
    {
        return $this->support->errorFactory();
    }

    public function sessionFactory(): callable
    {
        return $this->state->sessionFactory();
    }

    public function dateFactory(): callable
    {
        return $this->support->dateFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->state->cookieFactory();
    }

    public function cacheFactory(): callable
    {
        return $this->cache->cacheFactory();
    }
}
