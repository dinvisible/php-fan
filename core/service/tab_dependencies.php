<?php

declare(strict_types=1);

namespace fan\core\service;

final class tab_dependencies
{
    public function __construct(
        public readonly ?object $runtime = null,
        public readonly ?object $tabState = null,
        public readonly tab_service_factories $serviceFactories = new tab_service_factories(),
        public readonly tab_view_factories $viewFactories = new tab_view_factories(),
        public readonly tab_utility_dependencies $utilityDependencies = new tab_utility_dependencies(),
        public readonly tab_storage_dependencies $storageDependencies = new tab_storage_dependencies()
    ) {
    }

    public static function fromLegacy(
        ?object $runtime = null,
        ?callable $roleFactory = null,
        ?callable $transferFactory = null,
        ?callable $configFactory = null,
        ?callable $headerFactory = null,
        ?callable $applicationFactory = null,
        ?callable $debugFactory = null,
        ?callable $jsonFactory = null,
        ?callable $dataLoaderFactory = null,
        ?callable $templateFactory = null,
        ?callable $errorFactory = null,
        ?callable $logFactory = null,
        ?callable $cookieFactory = null,
        ?callable $reflectorFactory = null,
        ?callable $entityFactory = null,
        ?callable $formFactory = null,
        ?callable $pagerFactory = null,
        ?callable $obfuscatorFactory = null,
        ?callable $imageModifyFactory = null,
        ?callable $databaseFactory = null,
        ?callable $userFactory = null,
        ?callable $dateFactory = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $delegateFactory = null,
        ?callable $viewParserFactory = null,
        ?callable $blockFactory = null,
        ?callable $blockExceptionFactory = null,
        ?callable $metaRowFactory = null,
        ?callable $uploadSizeLimitProvider = null,
        ?object $aliasFileStorage = null,
        ?callable $viewDefinerFactory = null,
        ?callable $metaMakerFactory = null,
        ?callable $viewRouterFactory = null,
        ?callable $viewLoaderStateFactory = null,
        ?callable $metaMakerStateFactory = null,
        ?callable $arrayAdducer = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayValueReader = null,
        ?callable $arrayLikeChecker = null,
        ?callable $shortClassNameResolver = null,
        ?object $imageMetadataReader = null,
        ?object $errorLogWriter = null,
        ?object $blockFileStorage = null,
        ?object $metaFileStorage = null,
        ?object $projectToolFileStorage = null,
        ?object $rootHtmlFileStorage = null,
        ?object $tabState = null
    ): self {
        return new self(
            $runtime,
            $tabState,
            new tab_service_factories(
                $roleFactory,
                $transferFactory,
                $configFactory,
                $headerFactory,
                $applicationFactory,
                $debugFactory,
                $jsonFactory,
                $dataLoaderFactory,
                $templateFactory,
                $errorFactory,
                $logFactory,
                $cookieFactory,
                $reflectorFactory,
                $entityFactory,
                $formFactory,
                $pagerFactory,
                $obfuscatorFactory,
                $imageModifyFactory,
                $databaseFactory,
                $userFactory,
                $dateFactory,
                $phpArrayFileLoader
            ),
            new tab_view_factories(
                $delegateFactory,
                $viewParserFactory,
                $blockFactory,
                $blockExceptionFactory,
                $metaRowFactory,
                $uploadSizeLimitProvider,
                $viewDefinerFactory,
                $metaMakerFactory,
                $viewRouterFactory,
                $viewLoaderStateFactory,
                $metaMakerStateFactory
            ),
            new tab_utility_dependencies(
                $arrayAdducer,
                $recursiveMerger,
                $arrayValueReader,
                $arrayLikeChecker,
                $shortClassNameResolver
            ),
            new tab_storage_dependencies(
                $aliasFileStorage,
                $imageMetadataReader,
                $errorLogWriter,
                $blockFileStorage,
                $metaFileStorage,
                $projectToolFileStorage,
                $rootHtmlFileStorage
            )
        );
    }
}
