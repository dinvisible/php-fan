<?php

declare(strict_types=1);

namespace fan\core\di;


final class application_container_registrar_defaults_provider_factory
{
    private \Closure $registrarDefaultsProviderFactoryProvider;

    public function __construct(
        ?callable $registrarDefaultsProviderFactory = null,
        ?callable $registrarDefaultsProviderFactoryProvider = null
    ) {
        $this->registrarDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $registrarDefaultsProviderFactory !== null
                ? static fn(): callable => $registrarDefaultsProviderFactory
                : ($registrarDefaultsProviderFactoryProvider
                    ?? static fn(): callable => new application_service_registrar_defaults_provider_factory())
        );
    }

    public function __invoke(): callable
    {
        $registrarDefaultsProviderFactory = ($this->registrarDefaultsProviderFactoryProvider)();

        return static fn(): application_service_registrar_defaults_provider => $registrarDefaultsProviderFactory();
    }
}
