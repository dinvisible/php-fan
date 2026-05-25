<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_factory_bundle
{
    public function __construct(
        public \Closure $requestInputFactory,
        public \Closure $bootstrapOperationsFactory,
        public \Closure $phpArrayFileLoader,
        public \Closure $serializerOperationsFactory,
        public \Closure $configServiceFactory,
        public \Closure $bootstrapRuntimeServiceFactory,
        public \Closure $serviceEngineFactory,
        public ?\Closure $cacheEngineFactory,
        public ?\Closure $sessionEngineFactory,
        public \Closure $sessionServiceFactory,
        public ?\Closure $userEngineFactory,
        public \Closure $userServiceFactory,
        public \Closure $timerProgramFactory,
        public \Closure $timerServiceFactory,
        public \Closure $plainControllerFactory,
        public \Closure $plainServiceFactory,
        public \Closure $localeServiceFactory,
        public \Closure $matcherItemFactory,
        public \Closure $matcherItemComponentFactory,
        public \Closure $tabDelegateFactory,
        public \Closure $tabViewParserFactory,
        public \Closure $entityDesignerFactory,
        public \Closure $entityEncapsulantFactory,
        public \Closure $curlServiceFactory,
        public \Closure $restServiceFactory,
        public \Closure $cookieServiceFactory,
        public \Closure $pagerServiceFactory,
        public \Closure $obfuscatorServiceFactory,
        public \Closure $imageModifyServiceFactory,
        public \Closure $soapServiceFactory,
        public \Closure $dateServiceFactory,
        public \Closure $modelEntityFactory,
        public ?\Closure $modelRowFactory,
        public ?\Closure $modelRowsetFactory,
        public \Closure $modelRequestFactory,
        public \Closure $fileSystemServiceFactory,
        public \Closure $jsonServiceFactory,
        public \Closure $cacheServiceFactory,
        public \Closure $blockFactory,
        public \Closure $blockExceptionFactory,
        public \Closure $translationServiceFactory,
        public \Closure $requestServiceFactory,
        public \Closure $roleServiceFactory,
        public \Closure $tabServiceFactory,
        public \Closure $errorServiceFactory,
        public \Closure $reflectorServiceFactory,
        public \Closure $applicationServiceFactory,
        public \Closure $debugServiceFactory,
        public \Closure $headerServiceFactory,
        public \Closure $matcherServiceFactory,
        public \Closure $configuredServiceFactory
    ) {
    }

    public static function fromProviders(
        application_runtime_factory_provider $runtimeFactoryProvider,
        application_model_factory_provider $modelFactoryProvider,
        service_factory_registry $serviceFactoryRegistry,
        application_service_factory_options $factoryOptions
    ): self {
        $configuredServiceFactory = \Closure::fromCallable(
            $factoryOptions->configuredServiceFactory ?? $runtimeFactoryProvider->configuredConstructionBoundary()
        );

        return new self(
            \Closure::fromCallable($factoryOptions->requestInputFactory ?? $runtimeFactoryProvider->requestInputFactory()),
            \Closure::fromCallable($factoryOptions->bootstrapOperationsFactory ?? $runtimeFactoryProvider->bootstrapOperationsFactory()),
            \Closure::fromCallable($factoryOptions->phpArrayFileLoader ?? $runtimeFactoryProvider->phpArrayFileLoader()),
            \Closure::fromCallable($factoryOptions->serializerOperationsFactory ?? $runtimeFactoryProvider->serializerOperationsFactory()),
            \Closure::fromCallable($factoryOptions->configServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'configServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->bootstrapRuntimeServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'bootstrapRuntimeServiceFactory')),
            \Closure::fromCallable($factoryOptions->serviceEngineFactory ?? self::registryFactory($serviceFactoryRegistry, 'serviceEngineFactory', $configuredServiceFactory)),
            $factoryOptions->cacheEngineFactory === null ? null : \Closure::fromCallable($factoryOptions->cacheEngineFactory),
            $factoryOptions->sessionEngineFactory === null ? null : \Closure::fromCallable($factoryOptions->sessionEngineFactory),
            \Closure::fromCallable($factoryOptions->sessionServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'sessionServiceFactory', $configuredServiceFactory)),
            $factoryOptions->userEngineFactory === null ? null : \Closure::fromCallable($factoryOptions->userEngineFactory),
            \Closure::fromCallable($factoryOptions->userServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'userServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->timerProgramFactory ?? self::registryFactory($serviceFactoryRegistry, 'timerProgramFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->timerServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'timerServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->plainControllerFactory ?? self::registryFactory($serviceFactoryRegistry, 'plainControllerFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->plainServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'plainServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->localeServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'localeServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->matcherItemFactory ?? self::registryFactory($serviceFactoryRegistry, 'matcherItemFactory')),
            \Closure::fromCallable($factoryOptions->matcherItemComponentFactory ?? self::registryFactory($serviceFactoryRegistry, 'matcherItemComponentFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->tabDelegateFactory ?? self::registryFactory($serviceFactoryRegistry, 'tabDelegateFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->tabViewParserFactory ?? self::registryFactory($serviceFactoryRegistry, 'tabViewParserFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->entityDesignerFactory ?? $modelFactoryProvider->entityDesignerFactory($configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->entityEncapsulantFactory ?? $modelFactoryProvider->entityEncapsulantFactory($configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->curlServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'curlServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->restServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'restServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->cookieServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'cookieServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->pagerServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'pagerServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->obfuscatorServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'obfuscatorServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->imageModifyServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'imageModifyServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->soapServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'soapServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->dateServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'dateServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->modelEntityFactory ?? $modelFactoryProvider->modelEntityFactory($configuredServiceFactory)),
            $factoryOptions->modelRowFactory === null ? null : \Closure::fromCallable($factoryOptions->modelRowFactory),
            $factoryOptions->modelRowsetFactory === null ? null : \Closure::fromCallable($factoryOptions->modelRowsetFactory),
            \Closure::fromCallable($factoryOptions->modelRequestFactory ?? $modelFactoryProvider->modelRequestFactory($configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->fileSystemServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'fileSystemServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->jsonServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'jsonServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->cacheServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'cacheServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->blockFactory ?? self::registryFactory($serviceFactoryRegistry, 'blockFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->blockExceptionFactory ?? self::registryFactory($serviceFactoryRegistry, 'blockExceptionFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->translationServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'translationServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->requestServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'requestServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->roleServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'roleServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->tabServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'tabServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->errorServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'errorServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->reflectorServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'reflectorServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->applicationServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'applicationServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->debugServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'debugServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->headerServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'headerServiceFactory', $configuredServiceFactory)),
            \Closure::fromCallable($factoryOptions->matcherServiceFactory ?? self::registryFactory($serviceFactoryRegistry, 'matcherServiceFactory', $configuredServiceFactory)),
            $configuredServiceFactory
        );
    }

    private static function registryFactory(service_factory_registry $registry, string $serviceName, mixed ...$arguments): callable
    {
        return ($registry->get($serviceName))(...$arguments);
    }
}
