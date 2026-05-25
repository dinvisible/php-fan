<?php

declare(strict_types=1);

namespace fan\core\di;


final class application_container_factory_provider_defaults_provider_factory
{
    private \Closure $factoryProviderDefaultsProviderFactoryProvider;

    public function __construct(
        ?callable $factoryProviderDefaultsProviderFactory = null,
        ?callable $factoryProviderDefaultsProviderFactoryProvider = null
    ) {
        $this->factoryProviderDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $factoryProviderDefaultsProviderFactory !== null
                ? static fn(): callable => $factoryProviderDefaultsProviderFactory
                : ($factoryProviderDefaultsProviderFactoryProvider
                    ?? static fn(): callable => new application_factory_provider_defaults_provider_factory())
        );
    }

    public function __invoke(): callable
    {
        $factoryProviderDefaultsProviderFactory = ($this->factoryProviderDefaultsProviderFactoryProvider)();

        return static fn(
            application_adapter_registry $adapterRegistry
        ): application_factory_provider_defaults_provider => $factoryProviderDefaultsProviderFactory($adapterRegistry);
    }
}
