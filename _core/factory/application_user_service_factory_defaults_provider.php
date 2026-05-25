<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_service_factory_defaults_provider
{
    private \Closure $userServiceFactoryFactory;

    public function __construct(callable $userServiceFactoryFactory)
    {
        $this->userServiceFactoryFactory = \Closure::fromCallable($userServiceFactoryFactory);
    }

    public function userServiceFactory(): callable
    {
        return $this->userServiceFactoryFactory;
    }
}
