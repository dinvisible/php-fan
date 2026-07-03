<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\base\service_dependencies;
use fan\core\service\tab as core_tab_service;
use fan\core\service\tab_dependencies;

final class tab_service_factory
{
    private \Closure $configuredServiceFactory;
    private \Closure $viewDefinerFactory;
    private \Closure $metaMakerFactory;
    private \Closure $viewRouterFactory;
    private \Closure $viewLoaderStateFactory;
    private \Closure $metaMakerStateFactory;

    public function __construct(
        callable $configuredServiceFactory,
        callable $viewDefinerFactory,
        callable $metaMakerFactory,
        callable $viewRouterFactory,
        callable $viewLoaderStateFactory,
        callable $metaMakerStateFactory
    ) {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
        $this->viewDefinerFactory = \Closure::fromCallable($viewDefinerFactory);
        $this->metaMakerFactory = \Closure::fromCallable($metaMakerFactory);
        $this->viewRouterFactory = \Closure::fromCallable($viewRouterFactory);
        $this->viewLoaderStateFactory = \Closure::fromCallable($viewLoaderStateFactory);
        $this->metaMakerStateFactory = \Closure::fromCallable($metaMakerStateFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $matcher,
        object $request,
        object $locale,
        callable $sessionFactory,
        object $input,
        object $runtime,
        callable $roleFactory,
        callable $transferFactory,
        callable $configFactory,
        callable $headerFactory,
        callable $applicationFactory,
        callable $debugFactory,
        callable $jsonFactory,
        callable $dataLoaderFactory,
        callable $templateFactory,
        callable $errorFactory,
        ?callable $logFactory,
        callable $cookieFactory,
        callable $reflectorFactory,
        callable $entityFactory,
        ?callable $formFactory,
        callable $pagerFactory,
        callable $obfuscatorFactory,
        callable $imageModifyFactory,
        callable $databaseFactory,
        callable $userFactory,
        callable $dateFactory,
        object $tabState,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        callable $phpArrayFileLoader,
        callable $delegateFactory,
        callable $viewParserFactory,
        callable $blockFactory,
        callable $blockExceptionFactory,
        callable $metaRowFactory,
        object $aliasFileStorage,
        ?callable $arrayAdducer = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayValueReader = null,
        ?callable $classNameResolver = null,
        ?callable $arrayLikeChecker = null,
        ?callable $shortClassNameResolver = null,
        ?object $imageMetadataReader = null,
        ?object $errorLogWriter = null,
        ?object $blockFileStorage = null,
        ?object $metaFileStorage = null,
        ?object $projectToolFileStorage = null,
        ?object $rootHtmlFileStorage = null
    ): object {
        $arrayAdducer ??= static fn(mixed $value): array => \adduceToArray($value);
        $recursiveMerger ??= static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values);
        $arrayValueReader ??= static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default);
        $classNameResolver ??= static fn(object $object): string => \get_class_alt($object) ?? get_class($object);
        $arrayLikeChecker ??= static fn(mixed $value): bool => \is_array_alt($value);
        $shortClassNameResolver ??= static fn(object|string $object): string => \get_class_name($object) ?? (is_object($object) ? get_class($object) : $object);
        $tabDependencies = tab_dependencies::fromLegacy(
            $runtime,
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
            $phpArrayFileLoader,
            $delegateFactory,
            $viewParserFactory,
            $blockFactory,
            $blockExceptionFactory,
            $metaRowFactory,
            null,
            $aliasFileStorage,
            $this->viewDefinerFactory,
            $this->metaMakerFactory,
            $this->viewRouterFactory,
            $this->viewLoaderStateFactory,
            $this->metaMakerStateFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $arrayLikeChecker,
            $shortClassNameResolver,
            $imageMetadataReader,
            $errorLogWriter,
            $blockFileStorage,
            $metaFileStorage,
            $projectToolFileStorage,
            $rootHtmlFileStorage,
            $tabState
        );
        $serviceDependencies = service_dependencies::fromLegacy(
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            null,
            null,
            null,
            null,
            $classNameResolver,
            $arrayValueReader
        );
        $arguments = [
            $allowIni,
            $matcher,
            $request,
            $locale,
            $sessionFactory,
            $input,
            $runtime,
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
            $tabState,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpArrayFileLoader,
            $delegateFactory,
            $viewParserFactory,
            $blockFactory,
            $blockExceptionFactory,
            $metaRowFactory,
            $aliasFileStorage,
            $this->viewDefinerFactory,
            $this->metaMakerFactory,
            $this->viewRouterFactory,
            $this->viewLoaderStateFactory,
            $this->metaMakerStateFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $classNameResolver,
            $arrayLikeChecker,
            $shortClassNameResolver,
            $imageMetadataReader,
            $errorLogWriter,
            $blockFileStorage,
            $metaFileStorage,
            $projectToolFileStorage,
            $rootHtmlFileStorage
        ];

        if ($className === core_tab_service::class) {
            return new core_tab_service(
                $allowIni,
                $matcher,
                $request,
                $locale,
                $sessionFactory,
                $input,
                tabDependencies: $tabDependencies,
                serviceDependencies: $serviceDependencies
            );
        }

        return ($this->configuredServiceFactory)($className, $arguments);
    }

}
