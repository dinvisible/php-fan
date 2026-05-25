<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\meta\maker_state;


final class application_service_factory_defaults_provider_factory
{
    private \Closure $engineFactoryDefaultsProviderFactory;
    private \Closure $subFactoryDefaultsProviderFactory;
    private \Closure $coreFactoryDefaultsProviderFactory;
    private \Closure $clientFactoryDefaultsProviderFactory;
    private \Closure $infrastructureFactoryDefaultsProviderFactory;
    private \Closure $pagerFactoryDefaultsProviderFactory;
    private \Closure $utilityFactoryDefaultsProviderFactory;
    private \Closure $navigationFactoryDefaultsProviderFactory;
    private \Closure $contentFactoryDefaultsProviderFactory;
    private \Closure $sessionFactoryDefaultsProviderFactory;
    private \Closure $userFactoryDefaultsProviderFactory;
    private \Closure $serviceFactoryRegistryFactory;

    public function __construct(
        ?callable $engineFactoryDefaultsProviderFactory = null,
        ?callable $subFactoryDefaultsProviderFactory = null,
        ?callable $coreFactoryDefaultsProviderFactory = null,
        ?callable $clientFactoryDefaultsProviderFactory = null,
        ?callable $infrastructureFactoryDefaultsProviderFactory = null,
        ?callable $pagerFactoryDefaultsProviderFactory = null,
        ?callable $utilityFactoryDefaultsProviderFactory = null,
        ?callable $navigationFactoryDefaultsProviderFactory = null,
        ?callable $contentFactoryDefaultsProviderFactory = null,
        ?callable $sessionFactoryDefaultsProviderFactory = null,
        ?callable $userFactoryDefaultsProviderFactory = null,
        ?callable $serviceFactoryRegistryFactory = null
    )
    {
        $this->engineFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $engineFactoryDefaultsProviderFactory
                ?? static fn(application_adapter_registry $adapterRegistry): application_service_engine_factory_defaults_provider => (new application_service_engine_factory_defaults_provider_factory())($adapterRegistry)
        );
        $this->subFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $subFactoryDefaultsProviderFactory
                ?? static fn(): application_service_sub_factory_defaults_provider => (new application_service_sub_factory_defaults_provider_factory())()
        );
        $this->coreFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $coreFactoryDefaultsProviderFactory
                ?? static fn(): application_core_service_factory_defaults_provider => (new application_core_service_factory_defaults_provider_factory())()
        );
        $this->clientFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $clientFactoryDefaultsProviderFactory
                ?? static fn(): application_client_service_factory_defaults_provider => new application_client_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new curl_service_factory(
                        $configuredServiceFactory
                    ),
                    static fn(callable $configuredServiceFactory): callable => new rest_service_factory(
                        $configuredServiceFactory
                    ),
                    static fn(callable $configuredServiceFactory): callable => new cookie_service_factory(
                        $configuredServiceFactory
                    )
                )
        );
        $this->infrastructureFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $infrastructureFactoryDefaultsProviderFactory
                ?? static fn(): application_infrastructure_service_factory_defaults_provider => new application_infrastructure_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new config_service_factory(
                        $configuredServiceFactory,
                        null
                    ),
                    static fn(callable $configuredServiceFactory): callable => new file_system_service_factory(
                        $configuredServiceFactory
                    ),
                    static fn(callable $configuredServiceFactory): callable => new json_service_factory(
                        $configuredServiceFactory
                    ),
                    static fn(callable $configuredServiceFactory): callable => new cache_service_factory(
                        $configuredServiceFactory
                    )
                )
        );
        $this->pagerFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $pagerFactoryDefaultsProviderFactory
                ?? static fn(): application_pager_service_factory_defaults_provider => new application_pager_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new pager_service_factory(
                        $configuredServiceFactory
                    )
                )
        );
        $this->utilityFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $utilityFactoryDefaultsProviderFactory
                ?? static fn(): application_utility_service_factory_defaults_provider => new application_utility_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new obfuscator_service_factory(
                        $configuredServiceFactory
                    ),
                    static fn(callable $configuredServiceFactory): callable => new image_modify_service_factory(
                        $configuredServiceFactory
                    ),
                    static fn(callable $configuredServiceFactory): callable => new soap_service_factory(
                        $configuredServiceFactory
                    ),
                    static fn(callable $configuredServiceFactory): callable => new date_service_factory(
                        $configuredServiceFactory
                    )
                )
        );
        $this->navigationFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $navigationFactoryDefaultsProviderFactory
                ?? static fn(): application_navigation_service_factory_defaults_provider => new application_navigation_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new tab_service_factory(
                        $configuredServiceFactory,
                        new view_definer_factory(),
                        new meta_maker_factory(new delayed_meta_factory()),
                        new view_router_factory(new view_keeper_factory()),
                        new view_loader_state_factory(
                            new view_loader_json_keeper_factory(),
                            new view_loader_text_keeper_factory()
                        ),
                        static fn(): object => new maker_state()
                    )
                )
        );
        $this->contentFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $contentFactoryDefaultsProviderFactory
                ?? static fn(): application_content_service_factory_defaults_provider => new application_content_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new translation_service_factory(
                        $configuredServiceFactory
                    )
                )
        );
        $this->sessionFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $sessionFactoryDefaultsProviderFactory
                ?? static fn(): application_session_service_factory_defaults_provider => new application_session_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new session_service_factory(
                        $configuredServiceFactory
                    )
                )
        );
        $this->userFactoryDefaultsProviderFactory = \Closure::fromCallable(
            $userFactoryDefaultsProviderFactory
                ?? static fn(): application_user_service_factory_defaults_provider => new application_user_service_factory_defaults_provider(
                    static fn(callable $configuredServiceFactory): callable => new user_service_factory(
                        $configuredServiceFactory
                    )
                )
        );
        $this->serviceFactoryRegistryFactory = \Closure::fromCallable(
            $serviceFactoryRegistryFactory
                ?? static fn(array $factories): service_factory_registry => new service_factory_registry($factories)
        );
    }

    public function __invoke(application_adapter_registry $adapterRegistry): application_service_factory_defaults_provider
    {
        return new application_service_factory_defaults_provider(
            ($this->engineFactoryDefaultsProviderFactory)($adapterRegistry),
            ($this->subFactoryDefaultsProviderFactory)(),
            ($this->coreFactoryDefaultsProviderFactory)(),
            ($this->clientFactoryDefaultsProviderFactory)(),
            ($this->infrastructureFactoryDefaultsProviderFactory)(),
            ($this->pagerFactoryDefaultsProviderFactory)(),
            ($this->utilityFactoryDefaultsProviderFactory)(),
            ($this->navigationFactoryDefaultsProviderFactory)(),
            ($this->contentFactoryDefaultsProviderFactory)(),
            ($this->sessionFactoryDefaultsProviderFactory)(),
            ($this->userFactoryDefaultsProviderFactory)(),
            $this->serviceFactoryRegistryFactory
        );
    }
}
