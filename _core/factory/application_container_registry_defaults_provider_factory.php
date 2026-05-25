<?php

declare(strict_types=1);

namespace fan\core\di;


final class application_container_registry_defaults_provider_factory
{
    private \Closure $registryDefaultsProviderFactoryProvider;

    public function __construct(
        ?callable $registryDefaultsProviderFactory = null,
        ?callable $registryDefaultsProviderFactoryProvider = null
    ) {
        $this->registryDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $registryDefaultsProviderFactory !== null
                ? static function () use ($registryDefaultsProviderFactory): callable {
                    return $registryDefaultsProviderFactory;
                }
                : ($registryDefaultsProviderFactoryProvider
                    ?? static function (): callable {
                        return new application_registry_defaults_provider_factory();
                    })
        );
    }

    public function __invoke(): callable
    {
        $registryDefaultsProviderFactory = ($this->registryDefaultsProviderFactoryProvider)();

        return static function () use ($registryDefaultsProviderFactory): application_registry_defaults_provider {
            return $registryDefaultsProviderFactory();
        };
    }
}
