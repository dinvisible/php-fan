<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_factory_defaults_provider
{
    private \Closure $serviceFactoryRegistryFactory;

    public function __construct(
        private application_service_engine_factory_defaults_provider $engineFactoryDefaultsProvider,
        private application_service_sub_factory_defaults_provider $subFactoryDefaultsProvider,
        private application_core_service_factory_defaults_provider $coreFactoryDefaultsProvider,
        private application_client_service_factory_defaults_provider $clientFactoryDefaultsProvider,
        private application_infrastructure_service_factory_defaults_provider $infrastructureFactoryDefaultsProvider,
        private application_pager_service_factory_defaults_provider $pagerFactoryDefaultsProvider,
        private application_utility_service_factory_defaults_provider $utilityFactoryDefaultsProvider,
        private application_navigation_service_factory_defaults_provider $navigationFactoryDefaultsProvider,
        private application_content_service_factory_defaults_provider $contentFactoryDefaultsProvider,
        private application_session_service_factory_defaults_provider $sessionFactoryDefaultsProvider,
        private application_user_service_factory_defaults_provider $userFactoryDefaultsProvider,
        callable $serviceFactoryRegistryFactory
    ) {
        $this->serviceFactoryRegistryFactory = \Closure::fromCallable($serviceFactoryRegistryFactory);
    }

    public function applicationServiceFactoryRegistry(): service_factory_registry
    {
        $factory = $this->serviceFactoryRegistryFactory;

        return $factory([
            'bootstrapRuntimeServiceFactory' => $this->engineFactoryDefaultsProvider->bootstrapRuntimeServiceFactory(),
            'serviceEngineFactory' => $this->engineFactoryDefaultsProvider->serviceEngineFactory(),
            'configServiceFactory' => $this->infrastructureFactoryDefaultsProvider->configServiceFactory(),
            'sessionServiceFactory' => $this->sessionFactoryDefaultsProvider->sessionServiceFactory(),
            'userServiceFactory' => $this->userFactoryDefaultsProvider->userServiceFactory(),
            'requestServiceFactory' => $this->coreFactoryDefaultsProvider->requestServiceFactory(),
            'roleServiceFactory' => $this->coreFactoryDefaultsProvider->roleServiceFactory(),
            'tabServiceFactory' => $this->navigationFactoryDefaultsProvider->tabServiceFactory(),
            'errorServiceFactory' => $this->coreFactoryDefaultsProvider->errorServiceFactory(),
            'reflectorServiceFactory' => $this->coreFactoryDefaultsProvider->reflectorServiceFactory(),
            'applicationServiceFactory' => $this->coreFactoryDefaultsProvider->applicationServiceFactory(),
            'debugServiceFactory' => $this->coreFactoryDefaultsProvider->debugServiceFactory(),
            'headerServiceFactory' => $this->coreFactoryDefaultsProvider->headerServiceFactory(),
            'matcherServiceFactory' => $this->coreFactoryDefaultsProvider->matcherServiceFactory(),
            'timerServiceFactory' => $this->coreFactoryDefaultsProvider->timerServiceFactory(),
            'plainServiceFactory' => $this->coreFactoryDefaultsProvider->plainServiceFactory(),
            'localeServiceFactory' => $this->coreFactoryDefaultsProvider->localeServiceFactory(),
            'timerProgramFactory' => $this->coreFactoryDefaultsProvider->timerProgramFactory(),
            'curlServiceFactory' => $this->clientFactoryDefaultsProvider->curlServiceFactory(),
            'restServiceFactory' => $this->clientFactoryDefaultsProvider->restServiceFactory(),
            'cookieServiceFactory' => $this->clientFactoryDefaultsProvider->cookieServiceFactory(),
            'pagerServiceFactory' => $this->pagerFactoryDefaultsProvider->pagerServiceFactory(),
            'obfuscatorServiceFactory' => $this->utilityFactoryDefaultsProvider->obfuscatorServiceFactory(),
            'imageModifyServiceFactory' => $this->utilityFactoryDefaultsProvider->imageModifyServiceFactory(),
            'soapServiceFactory' => $this->utilityFactoryDefaultsProvider->soapServiceFactory(),
            'dateServiceFactory' => $this->utilityFactoryDefaultsProvider->dateServiceFactory(),
            'fileSystemServiceFactory' => $this->infrastructureFactoryDefaultsProvider->fileSystemServiceFactory(),
            'jsonServiceFactory' => $this->infrastructureFactoryDefaultsProvider->jsonServiceFactory(),
            'cacheServiceFactory' => $this->infrastructureFactoryDefaultsProvider->cacheServiceFactory(),
            'translationServiceFactory' => $this->contentFactoryDefaultsProvider->translationServiceFactory(),
            'matcherItemFactory' => $this->subFactoryDefaultsProvider->matcherItemFactory(),
            'matcherItemComponentFactory' => $this->subFactoryDefaultsProvider->matcherItemComponentFactory(),
            'tabDelegateFactory' => $this->subFactoryDefaultsProvider->tabDelegateFactory(),
            'tabViewParserFactory' => $this->subFactoryDefaultsProvider->tabViewParserFactory(),
            'plainControllerFactory' => $this->subFactoryDefaultsProvider->plainControllerFactory(),
            'blockFactory' => $this->subFactoryDefaultsProvider->blockFactory(),
            'blockExceptionFactory' => $this->subFactoryDefaultsProvider->blockExceptionFactory(),
            'cacheEngineFactory' => $this->engineFactoryDefaultsProvider->cacheEngineFactory(),
            'sessionEngineFactory' => $this->engineFactoryDefaultsProvider->sessionEngineFactory(),
            'userEngineFactory' => $this->engineFactoryDefaultsProvider->userEngineFactory(),
        ]);
    }
}
