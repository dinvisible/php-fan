<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_deferred_service_factory_provider_factory
{
    private \Closure $deferredServiceFactoryProviderFactory;

    public function __construct(?callable $deferredServiceFactoryProviderFactory = null)
    {
        $this->deferredServiceFactoryProviderFactory = \Closure::fromCallable(
            $deferredServiceFactoryProviderFactory
                ?? static fn(): application_deferred_service_factory_provider => new application_deferred_service_factory_provider()
        );
    }

    public function __invoke(): application_deferred_service_factory_provider
    {
        return ($this->deferredServiceFactoryProviderFactory)();
    }
}
