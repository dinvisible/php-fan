<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_service_factory_defaults_provider
{
    private \Closure $sessionServiceFactoryFactory;

    public function __construct(callable $sessionServiceFactoryFactory)
    {
        $this->sessionServiceFactoryFactory = \Closure::fromCallable($sessionServiceFactoryFactory);
    }

    public function sessionServiceFactory(): callable
    {
        return $this->sessionServiceFactoryFactory;
    }
}
