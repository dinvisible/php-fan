<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_factory_options
{
    public function __construct(
        public ?\Closure $requestInputFactory = null,
        public ?\Closure $bootstrapOperationsFactory = null,
        public ?\Closure $phpArrayFileLoader = null,
        public ?\Closure $serializerOperationsFactory = null,
        public ?\Closure $bootstrapRuntimeServiceFactory = null,
        public ?\Closure $serviceEngineFactory = null,
        public ?\Closure $cacheEngineFactory = null,
        public ?\Closure $sessionEngineFactory = null,
        public ?\Closure $sessionServiceFactory = null,
        public ?\Closure $userEngineFactory = null,
        public ?\Closure $timerProgramFactory = null,
        public ?\Closure $timerServiceFactory = null,
        public ?\Closure $plainControllerFactory = null,
        public ?\Closure $plainServiceFactory = null,
        public ?\Closure $localeServiceFactory = null,
        public ?\Closure $matcherItemFactory = null,
        public ?\Closure $matcherItemComponentFactory = null,
        public ?\Closure $tabDelegateFactory = null,
        public ?\Closure $tabViewParserFactory = null,
        public ?\Closure $entityDesignerFactory = null,
        public ?\Closure $entityEncapsulantFactory = null,
        public ?\Closure $modelEntityFactory = null,
        public ?\Closure $modelRowFactory = null,
        public ?\Closure $modelRowsetFactory = null,
        public ?\Closure $modelRequestFactory = null,
        public ?\Closure $fileSystemServiceFactory = null,
        public ?\Closure $jsonServiceFactory = null,
        public ?\Closure $cacheServiceFactory = null,
        public ?\Closure $blockFactory = null,
        public ?\Closure $blockExceptionFactory = null,
        public ?\Closure $translationServiceFactory = null,
        public ?\Closure $requestServiceFactory = null,
        public ?\Closure $roleServiceFactory = null,
        public ?\Closure $tabServiceFactory = null,
        public ?\Closure $errorServiceFactory = null,
        public ?\Closure $reflectorServiceFactory = null,
        public ?\Closure $applicationServiceFactory = null,
        public ?\Closure $debugServiceFactory = null,
        public ?\Closure $headerServiceFactory = null,
        public ?\Closure $matcherServiceFactory = null,
        public ?\Closure $configServiceFactory = null,
        public ?\Closure $curlServiceFactory = null,
        public ?\Closure $restServiceFactory = null,
        public ?\Closure $cookieServiceFactory = null,
        public ?\Closure $pagerServiceFactory = null,
        public ?\Closure $obfuscatorServiceFactory = null,
        public ?\Closure $imageModifyServiceFactory = null,
        public ?\Closure $soapServiceFactory = null,
        public ?\Closure $dateServiceFactory = null,
        public ?\Closure $userServiceFactory = null,
        public ?\Closure $configuredServiceFactory = null
    ) {
    }

    /**
     * @param array<string, callable|null> $overrides
     */
    public static function fromCallables(array $overrides): self
    {
        return new self(
            requestInputFactory: self::closure($overrides['requestInputFactory'] ?? null),
            bootstrapOperationsFactory: self::closure($overrides['bootstrapOperationsFactory'] ?? null),
            phpArrayFileLoader: self::closure($overrides['phpArrayFileLoader'] ?? null),
            serializerOperationsFactory: self::closure($overrides['serializerOperationsFactory'] ?? null),
            bootstrapRuntimeServiceFactory: self::closure($overrides['bootstrapRuntimeServiceFactory'] ?? null),
            serviceEngineFactory: self::closure($overrides['serviceEngineFactory'] ?? null),
            cacheEngineFactory: self::closure($overrides['cacheEngineFactory'] ?? null),
            sessionEngineFactory: self::closure($overrides['sessionEngineFactory'] ?? null),
            sessionServiceFactory: self::closure($overrides['sessionServiceFactory'] ?? null),
            userEngineFactory: self::closure($overrides['userEngineFactory'] ?? null),
            timerProgramFactory: self::closure($overrides['timerProgramFactory'] ?? null),
            timerServiceFactory: self::closure($overrides['timerServiceFactory'] ?? null),
            plainControllerFactory: self::closure($overrides['plainControllerFactory'] ?? null),
            plainServiceFactory: self::closure($overrides['plainServiceFactory'] ?? null),
            localeServiceFactory: self::closure($overrides['localeServiceFactory'] ?? null),
            matcherItemFactory: self::closure($overrides['matcherItemFactory'] ?? null),
            matcherItemComponentFactory: self::closure($overrides['matcherItemComponentFactory'] ?? null),
            tabDelegateFactory: self::closure($overrides['tabDelegateFactory'] ?? null),
            tabViewParserFactory: self::closure($overrides['tabViewParserFactory'] ?? null),
            entityDesignerFactory: self::closure($overrides['entityDesignerFactory'] ?? null),
            entityEncapsulantFactory: self::closure($overrides['entityEncapsulantFactory'] ?? null),
            modelEntityFactory: self::closure($overrides['modelEntityFactory'] ?? null),
            modelRowFactory: self::closure($overrides['modelRowFactory'] ?? null),
            modelRowsetFactory: self::closure($overrides['modelRowsetFactory'] ?? null),
            modelRequestFactory: self::closure($overrides['modelRequestFactory'] ?? null),
            fileSystemServiceFactory: self::closure($overrides['fileSystemServiceFactory'] ?? null),
            jsonServiceFactory: self::closure($overrides['jsonServiceFactory'] ?? null),
            cacheServiceFactory: self::closure($overrides['cacheServiceFactory'] ?? null),
            blockFactory: self::closure($overrides['blockFactory'] ?? null),
            blockExceptionFactory: self::closure($overrides['blockExceptionFactory'] ?? null),
            translationServiceFactory: self::closure($overrides['translationServiceFactory'] ?? null),
            requestServiceFactory: self::closure($overrides['requestServiceFactory'] ?? null),
            roleServiceFactory: self::closure($overrides['roleServiceFactory'] ?? null),
            tabServiceFactory: self::closure($overrides['tabServiceFactory'] ?? null),
            errorServiceFactory: self::closure($overrides['errorServiceFactory'] ?? null),
            reflectorServiceFactory: self::closure($overrides['reflectorServiceFactory'] ?? null),
            applicationServiceFactory: self::closure($overrides['applicationServiceFactory'] ?? null),
            debugServiceFactory: self::closure($overrides['debugServiceFactory'] ?? null),
            headerServiceFactory: self::closure($overrides['headerServiceFactory'] ?? null),
            matcherServiceFactory: self::closure($overrides['matcherServiceFactory'] ?? null),
            configServiceFactory: self::closure($overrides['configServiceFactory'] ?? null),
            curlServiceFactory: self::closure($overrides['curlServiceFactory'] ?? null),
            restServiceFactory: self::closure($overrides['restServiceFactory'] ?? null),
            cookieServiceFactory: self::closure($overrides['cookieServiceFactory'] ?? null),
            pagerServiceFactory: self::closure($overrides['pagerServiceFactory'] ?? null),
            obfuscatorServiceFactory: self::closure($overrides['obfuscatorServiceFactory'] ?? null),
            imageModifyServiceFactory: self::closure($overrides['imageModifyServiceFactory'] ?? null),
            soapServiceFactory: self::closure($overrides['soapServiceFactory'] ?? null),
            dateServiceFactory: self::closure($overrides['dateServiceFactory'] ?? null),
            userServiceFactory: self::closure($overrides['userServiceFactory'] ?? null),
            configuredServiceFactory: self::closure($overrides['configuredServiceFactory'] ?? null)
        );
    }

    private static function closure(?callable $factory): ?\Closure
    {
        return $factory === null ? null : \Closure::fromCallable($factory);
    }
}
