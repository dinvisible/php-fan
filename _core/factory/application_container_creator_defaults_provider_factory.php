<?php

declare(strict_types=1);

namespace fan\core\di;


final class application_container_creator_defaults_provider_factory
{
    private \Closure $creatorDefaultsProviderFactoryProvider;

    public function __construct(
        ?callable $creatorDefaultsProviderFactory = null,
        ?callable $creatorDefaultsProviderFactoryProvider = null
    ) {
        $this->creatorDefaultsProviderFactoryProvider = \Closure::fromCallable(
            $creatorDefaultsProviderFactory !== null
                ? static fn(): callable => $creatorDefaultsProviderFactory
                : ($creatorDefaultsProviderFactoryProvider
                    ?? static fn(): callable => new application_service_creator_defaults_provider_factory())
        );
    }

    public function __invoke(): callable
    {
        $creatorDefaultsProviderFactory = ($this->creatorDefaultsProviderFactoryProvider)();

        return static fn(): application_service_creator_defaults_provider => $creatorDefaultsProviderFactory();
    }
}
