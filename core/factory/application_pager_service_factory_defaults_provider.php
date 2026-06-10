<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_service_factory_defaults_provider
{
    private \Closure $pagerServiceFactoryFactory;

    public function __construct(callable $pagerServiceFactoryFactory)
    {
        $this->pagerServiceFactoryFactory = \Closure::fromCallable($pagerServiceFactoryFactory);
    }

    public function pagerServiceFactory(): callable
    {
        return $this->pagerServiceFactoryFactory;
    }

}
