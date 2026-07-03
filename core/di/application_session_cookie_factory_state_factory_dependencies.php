<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_cookie_factory_state_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function cookieFactory(): callable
    {
        return fn(mixed $path, mixed $domain, bool $secure = true): mixed => $this->container->get(
            service_id::COOKIE,
            $path,
            $domain,
            $secure
        );
    }
}
