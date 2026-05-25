<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_registry_defaults_provider
{
    private ?application_adapter_registry $adapterRegistry = null;

    private \Closure $adapterRegistryFactory;

    private \Closure $stateRegistryFactory;

    public function __construct(
        private application_adapter_registry_defaults_provider $adapterRegistryDefaultsProvider,
        callable $adapterRegistryFactory,
        callable $stateRegistryFactory
    ) {
        $this->adapterRegistryFactory = \Closure::fromCallable($adapterRegistryFactory);
        $this->stateRegistryFactory = \Closure::fromCallable($stateRegistryFactory);
    }

    public function applicationAdapterRegistry(): application_adapter_registry
    {
        $factory = $this->adapterRegistryFactory;

        return $this->adapterRegistry ??= $factory($this->adapterRegistryDefaultsProvider);
    }

    public function applicationStateRegistry(): application_state_registry
    {
        $factory = $this->stateRegistryFactory;

        return $factory();
    }
}
