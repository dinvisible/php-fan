<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_service_factory_defaults_provider
{
    private \Closure $tabServiceFactoryFactory;

    public function __construct(callable $tabServiceFactoryFactory)
    {
        $this->tabServiceFactoryFactory = \Closure::fromCallable($tabServiceFactoryFactory);
    }

    public function tabServiceFactory(): callable
    {
        return $this->tabServiceFactoryFactory;
    }
}
